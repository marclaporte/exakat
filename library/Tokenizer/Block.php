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

class Block extends TokenAuto {
    static public $operators = array('T_OPEN_CURLY');
    static public $atom = 'Sequence';
    
    public function _check() {
        $notToken = array('T_VARIABLE', 'T_DOLLAR',
                          'T_CLOSE_CURLY', // $x{1}{3},
                          'T_CLOSE_PARENTHESIS', 'T_OPEN_PARENTHESIS',// x(1){3},
                          'T_OPEN_BRACKET', 'T_CLOSE_BRACKET',  // $x[1]{3},
                          'T_OBJECT_OPERATOR', 'T_DOUBLE_COLON', 'T_AT',
                          'T_STRING', 'T_COMMA', 'T_DO',
                          'T_IF', 'T_ELSEIF', 'T_ELSE',
                          'T_FOR', 'T_FOREACH');

    // @doc {{ Block }}
        $this->conditions = array( -1 => array('token'   => 'T_OPEN_CURLY',
                                               'atom'    => 'none'),
                                    0 => array('token'   => self::$operators),
                                    1 => array('atom'    => 'yes'),
                                    2 => array('token'   => 'T_CLOSE_CURLY',
                                               'atom'    => 'none'),
                                    3 => array('token'   => 'T_CLOSE_CURLY'),
        );
        
        $this->actions = array('toBlock'      => true);
        $this->checkAuto();

    // @doc { Block }
        $this->conditions = array( -1 => array('notToken' => $notToken),
                                    0 => array('token'    => self::$operators,
                                               'property' => array('association' => 'none')),
                                    1 => array('atom'     => 'yes'),
                                    2 => array('token'    => 'T_CLOSE_CURLY',
                                               'atom'     => 'none'),
                                    3 => array('notToken' => array('T_ENDIF', 'T_ELSE', 'T_ELSEIF'))
        );

        $this->actions = array('toBlock'            => true,
                               'addAlwaysSemicolon' => 'a1'
                               );
        $this->checkAuto();

    // @doc : { Block } endif
        $this->conditions = array( -1 => array('notToken' => $notToken),
                                    0 => array('token'    => self::$operators,
                                               'property' => array('association' => 'none')),
                                    1 => array('atom'     => 'yes'),
                                    2 => array('token'    => 'T_CLOSE_CURLY',
                                               'atom'     => 'none'),
                                    3 => array('token'    => array('T_ENDIF', 'T_ELSE', 'T_ELSEIF'))
        );

        $this->actions = array('toBlock'      => true);
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

if (fullcode.getProperty('bracket')) {
    fullcode.setProperty('fullcode', "{ /**/ } ");
} else {
    fullcode.setProperty('fullcode', " /**/ ");
}

GREMLIN;
    }
}

?>
