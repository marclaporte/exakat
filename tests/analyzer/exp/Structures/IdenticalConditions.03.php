<?php

$expected     = array('$x == 3 || $x == 3 || $w <=> 4', 
                      '$x == 3 || $w <=> 4 || $x == 3', 
                      '$x == 3 || ( $w <=> 4 || $x == 3 )', 
                      '$x == 3 || $x == 3'
);

$expected_not = array('$w <=> 4 && $x == 3 && $b');

?>