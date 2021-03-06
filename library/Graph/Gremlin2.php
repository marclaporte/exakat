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

namespace Graph;

class Gremlin2 extends Graph {
    private $scriptDir   = '';
    private $neo4j_host  = '';
    private $neo4j_auth  = '';
    private $neo4j_error = null; // error message. To be thrown when query only
    
    public function __construct(\Config $config) {
        parent::__construct($config);

        if (!file_exists($config->neo4j_folder)) {
            $this->neo4j_error = "Error in the path to the Neo4j folder. Please, check config/config.ini\n";
            return;
        }
        $this->scriptDir = $config->neo4j_folder.'/scripts/';

        if (!is_writable($this->scriptDir)) {
            $this->neo4j_error = "Can't write in '$this->scriptDir'. Exakat needs to write in this folder.\n";
            return;
        }

        $this->neo4j_host   = $config->neo4j_host.':'.$config->neo4j_port;

        if ($this->config->neo4j_login !== '') {
            $this->neo4j_auth   = base64_encode($this->config->neo4j_login.':'.$this->config->neo4j_password);
        }
    }

    public function query($query, $params = [], $load = []) {
        if ($this->neo4j_error !== null) {
            throw new \RuntimeException($this->neo4j_error);
        }

        $getString = 'script='.urlencode($query);
    
        if (!is_array($load)) {
            $load = [$load];
        }

        if (isset($params) && !empty($params)) {
            // Avoid changing arg10 to 'string'0 if query has more than 10 arguments.
            krsort($params);

            foreach($params as $name => $value) {
                if (is_string($value) && strlen($value) > 2000) {
                    $gremlin = "{ '".str_replace('$', '\\$', $value)."' }";

                    // what about factorise this below? 
                    $defName = 'a'.crc32($gremlin);
                    $defFileName = $this->scriptDir.$defName.'.gremlin';

                    if (!file_exists($defFileName)) {
                        $gremlin = 'def '.$defName.'() '.$gremlin;
                        file_put_contents($defFileName, $gremlin);
                    }

                    $query = str_replace($name, $defName.'()', $query);
                    $load[] = $defName;
                    unset($params[$name]);

                } elseif (is_array($value)) {
                    $value = array_map(function ($x) { return str_replace('$', '\\$', addslashes($x)); }, $value);
                    $gremlin = "{ ['".implode("','", $value)."'] }";
                    $defName = 'a'.crc32($gremlin);
                    $defFileName = $this->scriptDir.$defName.'.gremlin';

                    if (!file_exists($defFileName)) {
                        $gremlin = 'def '.$defName.'() '.$gremlin;
                        file_put_contents($defFileName, $gremlin);
                    }

                    $query = str_replace($name, $defName.'()', $query);
                    $load[] = $defName;
                    unset($params[$name]);
                } else { // a short string (less than 2000) : hardcoded
                    $query = str_replace($name, "'".addslashes($value)."'", $query);
                    unset($params[$name]);
                }
            }

            if (!empty($params)) {
                $getString .= '&params='.urlencode(json_encode($params));
            }
        }

        $getString = 'script='.urlencode($query);

        if (count($load) == 1) {
            $getString .= '&load='.urlencode(array_pop($load));
        } elseif (count($load) > 1) {
            $getString .= '&load='.implode(',', array_map('urlencode', $load));
        } // else (aka 0) is ignored (nothing to do)
    
        if (strlen($getString) > 20000) {
            echo 'Query string too big for GET (', strlen($getString), ")\n",
                 'Query : ',
                 $query,
                "\n\n",
                print_r($params, true);
            die();
        }

        $ch = curl_init();

        //set the url, number of POST vars, POST data
        $headers = array( 'Content-Length: '.strlen($getString),
                          'User-Agent: exakat',
                          'X-Stream: true');
        if (!empty($this->neo4j_auth)) {
            $headers[] = 'Authorization: Basic '.$this->neo4j_auth;
        }
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch,CURLOPT_URL,            'http://'.$this->neo4j_host.'/tp/gremlin/execute?'.$getString);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,  'GET');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_IPRESOLVE,      CURL_IPRESOLVE_V4);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);
    
        $result = json_decode($result);
        if (isset($result->errormessage)) {
            throw new \Exceptions\GremlinException($result->errormessage, $query);
        }

        return $result;
    }

    public function queryOne($query, $params = [], $load = []) {
        $res = $this->query($query, $params, $load);
        if (!is_object($res)) {
            die('Server is not responding');
        }
    
        if (isset($res->results) && isset($res->results[0])) {
            return $res->results[0];
        } else {
            echo 'Help needed in ', __METHOD__, "\n",
                 "Query : '", $query, "'\n",
                 var_dump($res, true);
            die();
        }
    }

    public function queryColumn($query, $params = [], $load = []) {
        $res = $this->query($query, $params, $load);
        $res = $res->results;
    
        $return = array();
        if(count($res) > 0) {
            foreach($res as $r) {
                $return[] = $r;
            }
        }
    
        return $return;
    }

    public function serverInfo() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://'.$this->config->neo4j_host);
        curl_setopt($ch, CURLOPT_PORT, $this->config->neo4j_port);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    public function getProjectName() {
        return $this->queryOne('g.V().has("atom", "Project").code');
    }
}

?>
