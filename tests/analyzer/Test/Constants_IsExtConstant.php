<?php

namespace Test;

include_once(dirname(dirname(dirname(__DIR__))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Constants_IsExtConstant extends Analyzer {
    /* 1 methods */

    public function testConstants_IsExtConstant01()  { $this->generic_test('Constants_IsExtConstant.01'); }
}
?>