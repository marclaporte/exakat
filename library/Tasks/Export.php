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

class Export extends Tasks {
    public function run(\Config $config) {
        $queryTemplate = 'g.V.as("x").except([g.v(0)])';

        $result = gremlin_query($queryTemplate);
        $vertices = (array) $result->results;

        $V = array();
        foreach($vertices as $v) {
            $x = $v->_id;
            $V[$x] =  (array) $v;
    
            if (isset($V[$x]['root'])) {
                $root = $x;
            }
        }

        $queryTemplate .= '.outE()';
        $result = gremlin_query($queryTemplate);
        $edges = (array) $result->results;

        $E = array();
        foreach($edges as $e) {
            $id = $e->_outV;
    
            if (!isset($E[$id])) {
                $E[$id] = array();
            }
    
            $endNodeId = $e->_inV;
            if(isset($E[$id][$endNodeId])) {
                $E[$id][$endNodeId] .= ', '.$e->_label;
            } else {
                $E[$id][$endNodeId] = $e->_label;
            }
        }

        if (!isset($root)) {
            die( "No root! Check the tree in Neo4j\n Aborting\n".number_format(memory_get_usage() / 1024 / 1024, 0).' Mo'. "\n");
        }

        if ($config->format == 'Dot') {
            $text = $this->display_dot($V, $E, $root);
        } elseif ($config->format  == 'Table') {
            $text = $this->display_table($V, $E, $root);
        } else {
            $text = $this->display_text($V, $E, $root);
        }

        if ($config->filename) {
            if ($config->format == 'Dot') {
                $fp = fopen($config->filename.'.dot', 'w+');
            } else {
                $fp = fopen($config->filename, 'w+');
            }
            fwrite($fp, $text);
            fclose($fp);
        } else {
            echo $text;
        }
    }
    
    private function display_text($V, $E, $root, $level = 0) {
        $r = '';

        if (isset($V[$root])) {
            $r .= str_repeat('  ', $level).$V[$root]['code']."\n";
        }
        if (isset($E[$root])) {
            foreach($E[$root] as $id => $label) {
                $r .= str_repeat('  ', $level)."Label : ".$label."\n".$this->display_text($V, $E, $id, $level + 1);
            }
        }

        return $r;
    }

    private function display_dot($V, $E, $root, $level = 0) {
        $r = '';

        foreach($V as $id => $v) {
            if (!isset($v['fullcode'])) {
                if (isset($v['code'])) {
                    $v['fullcode'] =  $v['code'];
                } else {
                    $v['fullcode'] =  'NO CODE PROVIDED';
                }
            }
             $R = $id.' [label="'.addslashes($v['fullcode']).'"';
             if (isset($v['atom'])) {
                $R .= ' shape=box ';
             }
             $R .= "];\n";
     
             $r .= $R;
        }

        foreach($E as $start => $e) {
            foreach($e as $end => $label) {
                if ($label == 'NEXT') {
                    $r .= "$start -> $end [label=\"$label\"  color=green];\n";
                } else {
                    $r .= "$start -> $end [label=\"$label\"];\n";
                }
            }
        }

        $r = " digraph graphname {
    $r
     }";

        return $r;
    }

    private function display_table($V, $E, $root, $level = 0) {
        $r = '<table>';

        foreach($V as $id => $v) {
            $row = array(highlight_string($v['code'], true));
            if (isset($v['atom'])) {
                $row[] = $v['atom'];
            } else {
                $row[] = 'No atom';
            }
            if (isset($v['token'])) {
                $row[] = $v['token'];
            } else {
                $row[] = 'No token';
            }
            if (isset($v['file'])) {
                $row[] = $v['file'];
            } else {
                $row[] = 'No file';
            }
            if (isset($v['order'])) {
                $row[] = $v['order'];
            } else {
                $row[] = '';
            }

            $row = '<td>'.join('</td><td>', $row).'</td>';
            $r .= "<tr>$row</tr>\n";
        }
        $r .= '</table>';

        return $r;
    }
}

?>
