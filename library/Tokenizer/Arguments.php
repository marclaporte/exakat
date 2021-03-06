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

class Arguments extends TokenAuto {
    static public $operators = array('T_COMMA');
    static public $atom = 'Arguments';

    static public $arguments = array('String', 'Integer', 'Boolean', 'Null', 'Addition',
                                     'Multiplication', 'Property', 'Methodcall', 'Coalesce',
                                     'Staticmethodcall', 'Staticconstant', 'Staticproperty',
                                     'New', 'Functioncall', 'Nsname', 'Identifier', 'Void',
                                     'Variable', 'Array', 'Assignation', 'Typehint', 'Keyvalue',
                                     'Float', 'Concatenation', 'Parenthesis', 'Cast', 'Sign',
                                     'Ternary', 'Function', 'Noscream', 'As', 'Magicconstant',
                                     'Logical', 'Preplusplus', 'Postplusplus', 'Not', 'Comparison',
                                     'Bitshift', 'Heredoc', 'Power', 'Shell', 'Arrayappend', 'Clone',
                                     'Include', 'Instanceof', 'Yield', 'Staticclass', 'Spaceship');

    public function _check() {
        // @note arguments separated by ,
        $this->conditions = array(-2 => array('token'   => array('T_OPEN_PARENTHESIS', 'T_FUNCTION', 'T_VARIABLE',
                                                                 'T_SEMICOLON', 'T_OPEN_CURLY', 'T_OPEN_BRACKET', 'T_ECHO')),
                                  -1 => array('atom'    => 'yes'),
                                   0 => array('token'   => self::$operators,
                                              'atom'    => 'none',
                                              'checkForArguments' => self::$arguments
                                              ),
                                   1 => array('atom'    => 'yes'),
                                   2 => array('token'   => array_merge(array('T_CLOSE_PARENTHESIS', 'T_CLOSE_TAG', 'T_CLOSE_BRACKET'),
                                                                       Logical::$operators, Comparison::$operators,
                                                                       Token::$alternativeEnding, RawString::$operators,
                                                                       Sequence::$operators, Arguments::$operators))
                                 );
        
        $this->actions = array('toArgument' => true,
                               'atom'       => 'Arguments');
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

s = [];
fullcode.out("ARGUMENT").sort{it.rank}._().each{ s.add(it.fullcode); };

if (s.size() == 0) {
    s = '';
} else {
    fullcode.setProperty("fullcode", s.join(", "));
}

// note : parenthesis are set in arguments (above), if needed.

GREMLIN;
    }
}
?>
