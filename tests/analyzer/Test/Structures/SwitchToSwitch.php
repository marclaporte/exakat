<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Structures_SwitchToSwitch extends Analyzer {
    /* 5 methods */

    public function testStructures_SwitchToSwitch01()  { $this->generic_test('Structures/SwitchToSwitch.01'); }
    public function testStructures_SwitchToSwitch02()  { $this->generic_test('Structures/SwitchToSwitch.02'); }
    public function testStructures_SwitchToSwitch03()  { $this->generic_test('Structures/SwitchToSwitch.03'); }
    public function testStructures_SwitchToSwitch04()  { $this->generic_test('Structures/SwitchToSwitch.04'); }
    public function testStructures_SwitchToSwitch05()  { $this->generic_test('Structures/SwitchToSwitch.05'); }
}
?>