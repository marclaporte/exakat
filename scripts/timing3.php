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


$rows = glob('projects/*');

$finals = [];
foreach($rows as $row) {
    $final = [basename($row)];
    
    if (!file_exists($row.'/log/project.timing.csv')) {
        continue;
    }
    $csv = file_get_contents($row.'/log/project.timing.csv');
    if (!preg_match('/Final\t([\d\.]+)\t([\d\.]+)/is' , $csv, $r)) {
        continue;
        print "$row\n";
        die();
    }
    $final[] = $r[2];
        
    $sqliteFilename = $row.'/datastore.sqlite';
    if (!file_exists($sqliteFilename)) {
        print "No $sqliteFilename\n";
        continue; 
    }
    
    $sqlite = new \Sqlite3($sqliteFilename);

    $res = $sqlite->query('SELECT * FROM hash WHERE key = "tokens";');
    $sqlRow = $res->fetchArray();
    $final[] = $sqlRow['value'];

    $res = $sqlite->query('SELECT * FROM hash WHERE key = "loc";');
    $sqlRow = $res->fetchArray();
    $final[] = $sqlRow['value'];
    
    $finals[] = $final;
}

$fp = fopen('timing3.csv', 'w+');
foreach($finals as $final) {
    fputcsv($fp, $final);
}
fclose($fp);

?>