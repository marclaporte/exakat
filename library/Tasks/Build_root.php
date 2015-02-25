<?php
/*
 * Copyright 2012-2015 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Gremlin\Query;

class Build_root implements Tasks {
    private $client = null;
    private $dir_root = '.';
    private $project_dir = '.';
    
    public function run(\Config $config) {
        $project = $config->project;
        $this->doc_root = $config->dir_root;
        $this->project_dir = $config->projects_root.'/projects/'.$config->project;

        $begin = microtime(true);
        $this->client = new Client();
        if ($config->verbose) { 
            print "Starting\n"; 
        }

        file_put_contents($this->project_dir.'/log/build_root.log', '');

        $this->logTime('Start');

        $result = $this->query("g.idx('racines')");
        if ($result->count() == 0) {
            $this->query("g.createIndex('racines', Vertex)");
        }
        if ($config->verbose) { print "created racines index\n"; }

        $this->logTime('g.idx("racines")');


        $this->query("g.dropIndex('atoms');");
        $this->query("g.createIndex('atoms', Vertex)");
        $this->logTime('g.idx("atoms")');


        if ($config->verbose) { print "g.idx('atoms') : filling\n"; }
        $query = "g.V.filter{it.atom in ['Integer', 'String', 'Identifier', 'Magicconstant',
                                         'Rawstring', 'Variable', 'Float', 'Boolean', 'Void', 'File']}.each{ 
                                         g.idx('atoms').put('atom', it.atom, it); }";
        $this->query($query);
        if ($config->verbose) { print "g.idx('atoms') : filled\n"; }
        $this->logTime('g.idx("atom")[["atom":"******"]] : filling');

        // creating the neo4j Index
        // @todo check this index
        $this->query("g.V.has('root', 'true').each{ g.idx('racines').put('token', 'ROOT', it); };");
        $this->logTime('g.idx("ROOT")');

        if ($config->verbose) { print "Indexing root done\n"; }

        // special case for the initial Rawstring. 
        $this->query("g.idx('racines')[['token':'ROOT']].has('atom','Sequence').each{ g.idx('atoms').put('atom', 'Sequence', it); };");
        $this->logTime('g.idx("racines") ROOT special');

        if ($config->verbose) { print "Creating index done\n"; }

        // creating the neo4j Index
        $this->query("g.V.has('index', 'true').each{ g.idx('racines').put('token', it.token, it); };");
        $this->logTime('g.idx("racines")[[token:***]] indexing');

        if ($config->verbose) { print "Indexing racines done\n"; }

        // calculating the Unicode blocks
        $this->query("g.idx('atoms')[['atom':'String']].filter{it.code.replaceAll(/^['\"]/, '').size() > 0}.each{ it.setProperty('unicode_block', it.code.replaceAll(/^['\"]/, '').toList().groupBy{ Character.UnicodeBlock.of( it as char ).toString() }.sort{-it.value.size}.find{true}.key.toString()); };");
        $this->query("g.idx('atoms')[['token':'Rawstring']].filter{it.code.replaceAll(/^['\"]/, '').size() > 0}.each{ it.setProperty('unicode_block', it.code.replaceAll(/^['\"]/, '').toList().groupBy{ Character.UnicodeBlock.of( it as char ).toString() }.sort{-it.value.size}.find{true}.key.toString()); };");
        $this->logTime('Unicodes block');

        if ($config->verbose) { print "String unicode done\n"; }

        // resolving the constants
        $extra_indices = array('constants', 'classes', 'interfaces', 'traits', 'functions', 'delete', 'namespaces', 'files');
        foreach($extra_indices as $indice) {
            $this->query("g.dropIndex('$indice');");
            $this->query("g.createIndex('$indice', Vertex)");
        }
        $this->logTime('g.idx("last index")');

        if ($config->verbose) { print "Creating index for constant, function and classes resolution.\n"; }

        $end = microtime(true);
    }

    private function query($query, $retry = 1) {
        $params = array('type' => 'IN');
        try {
            $GremlinQuery = new Query($this->client, $query, $params);
            return $GremlinQuery->getResultSet();
        } catch (Exception $e) {
            $fp = fopen($config->projects_root.'/'.$config->project.'/log/build_root.log', 'a');
            fwrite($fp, $query."\n");
            fwrite($fp, $e->getMessage());
            fclose($fp);
        
            if ($retry) {
                sleep (3);
                return query($query, 0);
            }
        
            die('died in '.__METHOD__."\n");
        }
    }

    private function logTime($step) {
        static $log, $begin, $end, $start;
    
        if ($log === null) {
            $log = fopen($this->project_dir.'/log/build_root.timing.csv', 'w+');
        }
        $end = microtime(true);
        if ($begin === null) { 
            $begin = $end; 
            $start = $end;
        }
    
        fwrite($log, $step."\t".($end - $begin)."\t".($end - $start)."\n");
        $begin = $end;
    }

}

?>