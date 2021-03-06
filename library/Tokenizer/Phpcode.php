<?php
/*
 * Copyright 2012-2016 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Tokenizer;

class Phpcode extends TokenAuto {
    static public $operators = array('T_OPEN_TAG');
    static public $atom = 'Phpcode';

    public function _check() {
        // Normal PHP script
        $this->conditions = array(0 => array('token' => Phpcode::$operators,
                                             'atom'  => 'none'),
                                  1 => array('atom'  => 'yes'),
                                  2 => array('token' => 'T_CLOSE_TAG'),
        );
        
        $this->actions = array('transform'     => array( 1 => 'CODE',
                                                         2 => 'DROP'),
                               'addSemicolon'  => 'it',
                               'atom'          => 'Phpcode',
                               'property'      => array('closing_tag' => true),
                               'cleanIndex'    => true);
        $this->checkAuto();

// <?php 3; ( with ; ) (No closing tag)
        $this->conditions = array(0 => array('token' => Phpcode::$operators,
                                             'atom'  => 'none'),
                                  1 => array('atom'  => array('Sequence', 'Namespace')),
                                  2 => array('token' => 'T_END'),
        );
        
        $this->actions = array('transform'     => array( 1 => 'CODE'),
                               'atom'          => 'Phpcode',
                               'property'      => array('closing_tag' => false),
                               'cleanIndex'    => true);
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN
if (fullcode.code == '<script language=\\"php\\">') {
    fullcode.fullcode = "<script language=\\"php\\">" + fullcode.out("CODE").next().fullcode;
    closing = "</script>";
} else if (fullcode.code in ['<%', '<%=']) {
    fullcode.fullcode = fullcode.code.trim() + " " + fullcode.out("CODE").next().fullcode;
    closing = "%>";
} else {
    fullcode.fullcode = fullcode.code.trim() + " " + fullcode.out("CODE").next().fullcode;
    closing = "?>";
}

if (fullcode.closing_tag == true) {
    fullcode.fullcode = fullcode.fullcode + closing;
}

GREMLIN;
    }
}

?>
