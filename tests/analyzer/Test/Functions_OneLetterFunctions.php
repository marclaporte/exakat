<?php

namespace Test;

include_once(dirname(dirname(dirname(__DIR__))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Functions_OneLetterFunctions extends Analyzer {
    /* 3 methods */

    public function testFunctions_OneLetterFunctions01()  { $this->generic_test('Functions_OneLetterFunctions.01'); }
    public function testFunctions_OneLetterFunctions02()  { $this->generic_test('Functions_OneLetterFunctions.02'); }
    public function testFunctions_OneLetterFunctions03()  { $this->generic_test('Functions_OneLetterFunctions.03'); }
}
?>