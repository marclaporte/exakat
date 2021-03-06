<?php

namespace Test;

include_once(dirname(dirname(dirname(dirname(__DIR__)))).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');
spl_autoload_register('Autoload::autoload_library');

class Extensions_Extgnupg extends Analyzer {
    /* 3 methods */

    public function testExtensions_Extgnupg01()  { $this->generic_test('Extensions_Extgnupg.01'); }
    public function testExtensions_Extgnupg02()  { $this->generic_test('Extensions_Extgnupg.02'); }
    public function testExtensions_Extgnupg03()  { $this->generic_test('Extensions_Extgnupg.03'); }
}
?>