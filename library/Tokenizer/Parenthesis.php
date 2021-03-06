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

class Parenthesis extends TokenAuto {
    static public $operators = array('T_OPEN_PARENTHESIS');
    static public $atom = 'Parenthesis';
    
    public function _check() {
        $operands    = 'yes';
        
        $prerequisite = array_merge(Functioncall::$operators, _Include::$operators,
                                    array('T_CLOSE_BRACKET', 'T_CLOSE_PARENTHESIS',
                                          'T_USE', 'T_CLOSE_CURLY', 'T_FUNCTION',
                                          'T_CLASS', 'T_OBJECT_OPERATOR', 'T_CONTINUE',
                                    ));
        // Removing  ( from Prerequisite
        $prerequisite = array_filter($prerequisite, function ($x) { return !in_array($x, array('T_OPEN_PARENTHESIS', 'T_FOREACH', 'T_FOR')); });

        // ( normal parenthesis )
        $this->conditions = array(-1 => array('notToken' => $prerequisite,
                                              'notAtom'  => array('Array', 'Property', 'Parenthesis')),
                                   0 => array('token'    => Parenthesis::$operators,
                                              'atom'     => 'none',
                                              'property' => array('association' => 'none')),
                                   1 => array('atom'     => $operands),
                                   2 => array('token'    => 'T_CLOSE_PARENTHESIS',
                                              'atom'     => 'none'),
        );
        
        $this->actions = array('transform'    => array( 1 => 'CODE',
                                                        2 => 'DROP'),
                               'atom'         => 'Parenthesis',
                               'cleanIndex'   => true,
                               'addSemicolon' => 'it',
                               'keepIndexed'  => true);
        $this->checkAuto();

        // ((parenthesis inside parenthesis))
        $this->conditions = array( -1 => array('notToken' => $prerequisite,
                                               'notAtom'  => array('Array', 'Property', 'Parenthesis')),
                                    0 => array('token'    => self::$operators),
                                    1 => array('atom'     => self::$atom),
                                    2 => array('token'    => 'T_CLOSE_PARENTHESIS'),
        );
        
        $this->actions = array('transform'    => array( 1 => 'CODE',
                                                        2 => 'DROP'),
                               'atom'         => 'Parenthesis',
                               'cleanIndex'   => true,
                               'addSemicolon' => 'it',
                               'keepIndexed'  => true);
        $this->checkAuto();

// this applies to situations like print ($a * $b) + $c; where parenthesis actually belong to the following expression.
        $this->conditions = array(-2 => array('notToken' => 'T_FUNCTION'),
                                  -1 => array('token'    => array_merge(array('T_ECHO', 'T_PRINT'), _Include::$operators)),
                                   0 => array('token'    => Parenthesis::$operators,
                                              'atom'     => 'none' ),
                                   1 => array('atom'     => $operands),
                                   2 => array('token'    => 'T_CLOSE_PARENTHESIS',
                                              'atom'     => 'none')
        );
        
        $this->actions = array('transform'    => array( 1 => 'CODE',
                                                        2 => 'DROP'),
                               'atom'         => 'Parenthesis',
                               'cleanIndex'   => true,
                               'addSemicolon' => 'it');
        
        $this->checkAuto();
        
        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

fullcode.setProperty('fullcode',  "( " + fullcode.out("CODE").next().getProperty('fullcode') + " )");

GREMLIN;
    }
}
?>
