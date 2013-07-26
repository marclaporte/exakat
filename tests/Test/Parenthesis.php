<?php

namespace Test;

include_once(dirname(dirname(__DIR__)).'/library/Autoload.php');
spl_autoload_register('Autoload::autoload_test');
spl_autoload_register('Autoload::autoload_phpunit');

class Parenthesis extends Tokenizeur {
    /* 9 methods */
    public function testParenthesis01()  { $this->generic_test('Parenthesis.01'); }
    public function testParenthesis02()  { $this->generic_test('Parenthesis.02'); }
    public function testParenthesis03()  { $this->generic_test('Parenthesis.03'); }
    public function testParenthesis04()  { $this->generic_test('Parenthesis.04'); }
    public function testParenthesis05()  { $this->generic_test('Parenthesis.05'); }
    public function testParenthesis06()  { $this->generic_test('Parenthesis.06'); }
    public function testParenthesis07()  { $this->generic_test('Parenthesis.07'); }
    public function testParenthesis08()  { $this->generic_test('Parenthesis.08'); }
    public function testParenthesis09()  { $this->generic_test('Parenthesis.09'); }
}
?>