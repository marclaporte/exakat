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
	Everyman\Neo4j\Gremlin;

class Magicnumber implements Tasks {
    private $client = null;
    
    public function run(\Config $config) {
        $project = $config->project;

        $this->client = new Client();
        
        $sqliteFile = $config->projects_root.'/projects/'.$config->project.'/magicnumber.sqlite';
        if (file_exists($sqliteFile)) {
            unlink($sqliteFile);
        }
        $sqlite = new \SQLite3($sqliteFile);

        $types = array('Integer', 'String', 'Float');

        foreach( $types as $type) {
            $query = <<<QUERY
        m = [:];
        g.idx('atoms')[['atom':'$type']].groupCount(m){it.code}{it.b+1}.iterate();
        m.findAll{it.value > 1}

QUERY;

            $sqlite->exec('CREATE TABLE '.$type.' (id INTEGER PRIMARY KEY, value STRING, count INTEGER)');
            $stmt = $sqlite->prepare('INSERT INTO '.$type.' (value, count) VALUES(:value, :count)');

            $res = $this->query($query);

            $total = 0;
            foreach($res as $k => $v) {
                $stmt->bindValue(':value', $k, SQLITE3_TEXT);
                $stmt->bindValue(':count', $v[0], SQLITE3_INTEGER);
                $stmt->execute();
                $total++;
            }
        
            if ($config->verbose) {
                print "$type : $total\n";
            }
        }
    }

    private function queryOne($query) {
        $r = query($query);
        return $r[0][0];
    }

    private function queryArray($query) {
        $return = array();
    
        $result = $this->query($query);
        foreach($result as $r) {
            $return[] = (array) $r[0];
        }
    
        return $return;
    }

    private function queryColumn($query) {
        $return = array();
    
        $result = query($query);
        foreach($result as $r) {
            $return[] = $r[0];
        }
    
        return $return;
    }

    private function query($query) {
        $params = array('type' => 'IN');
        $query = new Gremlin\Query($this->client, $query, $params);
        return $query->getResultSet();
    }

    private function table_encode($stats) {
        $html = "<html><body>";
    
        foreach($stats as $name => $value) {
            $html .= "<tr><td>$name</td><td>$value</td></tr>\n";
        }
    
        $html .= "</body></html>";
        return $html;
    }

    private function text_encode($stats) {
        $html = "Statistics for the whole server\n\n";
    
        foreach($stats as $name => $value) {
            if (is_array($value)) {
                $html .= "$name : ".join(" \n".str_repeat(' ', strlen("$name : ")), $value)."\n";
            } else {
                $html .= "$name : $value\n";
            }
        }
    
        $html .= "\n";
        return $html;
    }
}

?>