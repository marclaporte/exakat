<?php

$x[1.2] = 21;
$x['1'] = 2;
$x['09876543211'] = 2;
$x['9876543211'] = 2;
$x['098.76543211'] = 2;
$x['1a'] = 2;
$x[true] = 3;

$x[null] = 3;

$x['a'][1.2] = 21;
$x['a']['1'] = 2;
$x['a']['1a'] = 2;
$x['a'][true] = 3;

$x['a']['b']['c']['d'][1.2] = 21;
$x['a']['b'][3.4]['d']['c'] = 21;
$x[-3.4]['b']['e']['d']['c'] = 21;

$x[null] = 3;

var_dump($x);
var_dump(array_keys($x));

?>