<?php

$expected     = array('$x[true]', 
                      '$x[\'a\'][true]', 
                      '$x[null]', '$x[1.2]', 
                      '$x[\'a\'][\'b\'][\'c\'][\'d\'][1.2]',
                      '$x[null]', 
                      '$x[\'a\'][1.2]', 
                      '$x[\'a\'][\'b\'][3.4]', 
                      '$x[\'9876543211\']', 
                      '$x[\'1\']', 
                      '$x[\'a\'][\'1\']');

$expected_not = array('$x[\'09876543211\']',
                      '$x[\'098.76543211\']'
);

?>