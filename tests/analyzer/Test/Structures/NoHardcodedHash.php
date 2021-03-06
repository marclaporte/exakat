<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Structures_NoHardcodedHash extends Analyzer {
    /* 3 methods */

    public function testStructures_NoHardcodedHash01()  { $this->generic_test('Structures/NoHardcodedHash.01'); }
    public function testStructures_NoHardcodedHash02()  { $this->generic_test('Structures/NoHardcodedHash.02'); }
    public function testStructures_NoHardcodedHash03()  { $this->generic_test('Structures/NoHardcodedHash.03'); }
}
?>