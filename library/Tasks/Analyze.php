<?php
/*
 * Copyright 2012-2016 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Tasks;

class Analyze extends Tasks {
    public function run(\Config $config) {
        $project = $config->project;
        
        if ($project == 'default') {
            die("analyze require -p <project> option. Aborting\n");
        }

        if (!file_exists($config->projects_root.'/projects/'.$project)) {
            die("Project '$project' doesn't exist in projects folder. Aborting\n");
        }

        $this->checkTokenLimit();
        $begin = microtime(true);

        \Analyzer\Analyzer::$gremlinStatic = $this->gremlin;
        
        // Take this before we clean it up
        $rows = $this->datastore->getRow('analyzed');
        $analyzed = array();
        foreach($rows as $row) {
            $analyzed[$row['analyzer']] = $row['counts'];
        }

        if ($config->program !== null) {
            $analyzer = $config->program;
            if (\Analyzer\Analyzer::getClass($analyzer)) {
                $analyzers_class = array($analyzer);
            } else {
                $r = \Analyzer\Analyzer::getSuggestionClass($analyzer);
                if (count($r) > 0) {
                    echo 'did you mean : ', implode(', ', str_replace('_', '/', $r)), "\n";
                }
                die("No such class as '$analyzer'. Aborting\n");
            }
        } elseif ($config->thema !== null) {
            $thema = $config->thema;

            if (!$analyzers_class = \Analyzer\Analyzer::getThemeAnalyzers($thema)) {
                die("No such thema as '$thema'. Aborting\n");
            }
            $this->datastore->addRow('hash', array($config->thema => count($analyzers_class) ) );
        } else {
            die( "Usage :php exakat analyze -T <\"Thema\"> -p <project>\n
php exakat analyze -P <One/rule> -p <project>\n");
        }

        $this->log->log("Analyzing project $project");
        $this->log->log("Runnable analyzers\t".count($analyzers_class));

        if ($config->noDependencies) {
            $dependencies2 = $analyzers_class;
        } else {
            $dependencies = array();
            $dependencies2 = array();
            foreach($analyzers_class as $a) {
                $d = \Analyzer\Analyzer::getInstance($a);
                $configName = str_replace('/', '_', $a);
                if (null !== ($analyzerConfig = $config->$configName)) {
                    $d->setConfig($analyzerConfig);
                }
                $d = $d->dependsOn();
                if (empty($d)) {
                    $dependencies2[] = $a;
                } else {
                    $diff = array_diff($d, $dependencies2);
                    if (empty($diff)) {
                        $dependencies2[] = $a;
                    } else {
                        $dependencies[$a] = $diff;
                    }
                }
            }

            $c = count($dependencies) + 1;
            while(!empty($dependencies) && $c > count($dependencies)) {
                $c = count($dependencies);
                foreach($dependencies as $a => &$d) {
                    $diff = array_diff($d, $dependencies2);

                    foreach($diff as $k => $v) {
                        if (!isset($dependencies[$v])) {
                            $x = \Analyzer\Analyzer::getInstance($v);
                            if ($x === null) {
                                display( "No such dependency as '$v'. Ignoring\n");
                                continue;
                            }
                            $dep = $x->dependsOn();
                            if (count($dep) == 0) {
                                $dependencies2[] = $v;
                                ++$c;
                            } else {
                                $dependencies[$v] = $dep;
                                $c += count($dep) + 1;
                            }
                        } elseif (count($dependencies[$v]) == 0) {
                            $dependencies2[] = $v;
                            unset($diff[$k]);
                        }
                    }
        
                    if (empty($diff)) {
                        $dependencies2[] = $a;
                        unset($dependencies[$a]);
                    } else {
                        $d = $diff;
                    }
                }
                (unset) $d;
            }

            if (!empty($dependencies)) {
                die( "Dependencies depending on each other : can't finalize. Aborting\n".
                      print_r($dependencies, 1));
            }
        }

        $total_results = 0;
        $php = new \Phpexec($config->version);

        if (!$config->verbose && !$config->quiet) {
            print "Analyzing {$config->thema}{$config->program}\n";
            $progressBar = new \Progressbar(count($dependencies2) + 1);
        } else {
            display("Analyzing {$config->thema}{$config->program}\n");
        }

        foreach($dependencies2 as $analyzer_class) {
            if (isset($progressBar)) {
                echo $progressBar->drawCurrentProgress();
            }
            $begin = microtime(true);
            $analyzer = \Analyzer\Analyzer::getInstance($analyzer_class);
            $configName = str_replace(array('/', '\\'), '_', str_replace('Analyzer\\', '', $analyzer_class));
            if (null !== ($analyzerConfig = $config->$configName)) {
                $analyzer->setConfig($analyzerConfig);
            }
    
            if ($config->noRefresh && isset($analyzed[$analyzer_class])) {
                display( "$analyzer_class is already processed\n");
                continue 1;
            }
            $analyzer->init();
    
            if (!$analyzer->checkPhpVersion($config->phpversion)) {
                $analyzerQuoted = str_replace('\\', '\\\\', get_class($analyzer));
                
                $analyzer = str_replace('\\', '\\\\', $analyzer_class);
                
                $query = <<<GREMLIN
result = g.addVertex(null, [code:'Not Compatible With PhpVersion', fullcode:'Not Compatible With PhpVersion', virtual:true, notCompatibleWithPhpVersion:'$config->phpversion', token:'T_INCOMPATIBLE']);
index = g.addVertex(null, [analyzer:'$analyzerQuoted', analyzer:true, line:0, description:'Analyzer index for $analyzer', code:'', fullcode:'',  atom:'Index', token:'T_INDEX']);
g.idx('analyzers').put('analyzer', '$analyzerQuoted', index);
g.addEdge(index, result, 'ANALYZED');
GREMLIN;
                $this->gremlin->query($query);
                $this->datastore->addRow('analyzed', array($analyzer_class => -2 ) );

                display("$analyzer is not compatible with PHP version {$config->phpversion}. Ignoring\n");
            } elseif (!$analyzer->checkPhpConfiguration($php)) {
                $analyzerQuoted = str_replace('\\', '\\\\', get_class($analyzer));
                $analyzer = str_replace('\\', '\\\\', $analyzer_class);
            
                $query = <<<GREMLIN
result = g.addVertex(null, [code:'Not Compatible With Php Configuration', fullcode:'Not Compatible With Php Configuration', virtual:true, notCompatibleWithPhpConfiguration:'$config->phpversion', token:'T_INCOMPATIBLE']);
index = g.addVertex(null, [analyzer:'$analyzerQuoted', analyzer:true, line:0, description:'Analyzer index for $analyzer', code:'', fullcode:'',  atom:'Index', token:'T_INDEX']);
g.idx('analyzers').put('analyzer', '$analyzerQuoted', index);
g.addEdge(index, result, 'ANALYZED');
GREMLIN;
                $this->gremlin->query($query);
                $this->datastore->addRow('analyzed', array($analyzer_class => -1 ) );

                display( "$analyzer is not compatible with PHP configuration of this version. Ignoring\n");
            } else {
                $analyzer->run();

                $count = $analyzer->getRowCount();
                $processed = $analyzer->getProcessedCount();
                $queries = $analyzer->getQueryCount();
                $rawQueries = $analyzer->getRawQueryCount();
                $total_results += $count;
                display( "$analyzer_class fait ($count / $processed)\n");
                $end = microtime(true);
                $this->log->log("$analyzer_class\t".($end - $begin)."\t$count\t$processed\t$queries\t$rawQueries");
                // storing the number of row found in Hash table (datastore)
                $this->datastore->addRow('analyzed', array($analyzer_class => $count ) );
            }
        }
        
        // Final step
        if (isset($progressBar)) {
            echo $progressBar->drawCurrentProgress();
            unset($progressBar);
            print "\n";
        }

        display( "Done\n");
    }
}

?>
