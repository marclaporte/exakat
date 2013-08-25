<?php

namespace Tokenizer;

class ArgumentsNoComma extends TokenAuto {
    static public $operators = array('T_OPEN_PARENTHESIS');

    function _check() {
        // @note f(1) : no comma 
        $this->conditions = array(-1 => array('token' => array_merge(Functioncall::$operators, _Include::$operators, 
                                                         array(''))),
                                                         //T_CLOSE_CURLY
                                   0 => array('token' => ArgumentsNoComma::$operators,
                                              'atom'  => 'none'),
                                   1 => array('atom'  => Arguments::$operands_wa),
                                   2 => array('token' => 'T_CLOSE_PARENTHESIS',
                                              'atom'  => 'none'),
                                   3 => array('filterOut' => array('T_DOUBLECOLON', 'T_OPEN_PARENTHESIS')),
        );

        $this->actions = array('insertEdge'   => array(0 => array('Arguments' => 'ARGUMENT')));
        $this->checkAuto();

        $this->conditions = array(-1 => array('token' => array('T_PRINT', 'T_ECHO')),
                                   0 => array('token' => ArgumentsNoComma::$operators,
                                              'atom'  => 'none'),
                                   1 => array('atom'  => Arguments::$operands_wa),
                                   2 => array('token' => 'T_CLOSE_PARENTHESIS',
                                              'atom'  => 'none'),
                                   3 => array('filterOut2' => Token::$instruction_ending),
        );

        $this->actions = array('insertEdge'   => array(0 => array('Arguments' => 'ARGUMENT')));
        $this->checkAuto();

        return $this->checkRemaining();
    }
}
?>