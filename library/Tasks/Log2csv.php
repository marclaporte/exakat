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

class Log2csv extends Tasks {
    public function run(\Config $config) {
        if (!file_exists($config->projects_root.'/projects/'.$config->project.'/log/tokenizer.log')) {
            die( "No log/tokenizer.log. Aborting\n");
        }
        $in = fopen($config->projects_root.'/projects/'.$config->project.'/log/tokenizer.log', 'r');

        $matrix = array();
        $matrix2 = array();

        $result = array();
        $total = 0;
        $total_time = 0;
        while($row = fgets($in, 1000)) {
            if (preg_match("/Tokenizer\\\\(.*?)	(\d+\.\d+)	(\d+)/", $row, $r)) {
                ++$total;
                if (isset($result[$r[1]])) {
                    $result[$r[1]]['Time'] += $r[2];
                    $total_time += $r[2];
                    ++$result[$r[1]]['Times'];
            
                    $matrix[$r[1]][] = $r[2];
                    $matrix2[$r[1]][] = $r[3];
                } else {
                    $result[$r[1]] = array('Time' => $r[2], 'Times' => 1);

                    $matrix[$r[1]] = array($r[2]);
                    $matrix2[$r[1]] = array($r[3]);
                }
            }
        }
        fclose($in);
        
        $out = fopen($config->projects_root.'/projects/'.$config->project.'/log/tokenizer.csv', 'w+');
        fputcsv($out, array('Regex', 'Time', 'Times', str_replace('.', ',' , $total_time), 'Times_avg'));
        foreach($result as $k => $r) {
            array_unshift( $r, $k);
            $r['Time'] = str_replace('.', ',' ,$r['Time']);
            $r['Time_avg'] = number_format($r['Time'] / $total_time, 2, ',', '');
            $r['Times_avg'] = number_format($r['Time'] / $r['Times'], 2, ',', '');
    
            fputcsv($out, array_values($r));
        }
        fclose($out);

        $out = fopen($config->projects_root.'/projects/'.$config->project.'/log/matrix.csv', 'w+');
        $titles = array_keys($matrix);
        fputcsv($out, array_merge(array('id', 'count'), $titles));
        $max = 0;

        foreach($titles as $t) {
            $max = max($max, count($matrix[$t]));
        }

        for($i = 0; $i < $max; ++$i) {
            $row = array($i, 0);
            $count = 0;
    
            foreach($titles as $t) {
                $row[] = isset($matrix[$t][$i]) ? str_replace('.', ',', $matrix[$t][$i]) : 0;
                isset($matrix[$t][$i]) ? ++$count : 0;
            }
            $row[1] = $count;
            fputcsv($out, $row);
        }
        fclose($out);

        $out = fopen($config->projects_root.'/projects/'.$config->project.'/log/matrix2.csv', 'w+');
        $titles = array_keys($matrix2);
        fputcsv($out, array_merge(array('id', 'count'), $titles));
        $max = 0;

        foreach($titles as $t) {
            $max = max($max, count($matrix2[$t]));
        }

        for($i = 0; $i < $max; ++$i) {
            $row = array($i, 0);
            $count = 0;
    
            foreach($titles as $t) {
                $row[] = isset($matrix2[$t][$i]) ? str_replace('.', ',', $matrix2[$t][$i]) : 0;
                isset($matrix2[$t][$i]) ? ++$count : 0;
            }
            $row[1] = $count;

            fputcsv($out, $row);
        }
        fclose($out);
    }
}

?>
