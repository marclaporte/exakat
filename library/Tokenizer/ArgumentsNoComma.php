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

class ArgumentsNoComma extends Arguments {
    static public $operators = array('T_OPEN_PARENTHESIS');
    static public $atom = 'Arguments';

    public function _check() {
        // @note function f($a) {}
        $this->conditions = array(-2 => array('token'     => 'T_FUNCTION'),
                                  -1 => array('notToken'  => array_merge(Addition::$operators, Multiplication::$operators,
                                                                         Sequence::$operators, Arguments::$operators,
                                                                         array('T_OPEN_CURLY', 'T_OPEN_PARENTHESIS'))),
                                   0 => array('token'     => ArgumentsNoComma::$operators,
                                              'atom'      => 'none'),
                                   1 => array('atom'      => Arguments::$arguments),
                                   2 => array('token'     => 'T_CLOSE_PARENTHESIS',
                                              'atom'      => 'none')
        );

        $this->actions = array('insertVertex' => true,
                               'rank'         => array(1 => 0)
                               );
        $this->checkAuto();

        // @note f(1) : no comma
        $functioncalls = array_filter(Functioncall::$operatorsWithoutEcho, function ($x) { return $x != 'T_OPEN_PARENTHESIS'; });
        $this->conditions = array(-1 => array('token'     => array_merge($functioncalls,
                                                                 array('T_FUNCTION', 'T_DECLARE', 'T_USE', 'T_CLASS',
                                                                       'T_OBJECT_OPERATOR', 'T_DOUBLE_COLON', 'T_VARIABLE',
                                                                       'T_CATCH', 'T_DECLARE', 'T_ELSEIF'))),
                                   0 => array('token'     => ArgumentsNoComma::$operators,
                                              'atom'      => 'none'),
                                   1 => array('atom'      => Arguments::$arguments),
                                   2 => array('token'     => 'T_CLOSE_PARENTHESIS',
                                              'atom'      => 'none')
        );

        $this->actions = array('insertVertex' => true,
                               'rank'         => array(1 => 0)
                               );
        $this->checkAuto();

        // @note ($a->b())(c); special case for parenthesis as a function name
        $this->conditions = array(-1 => array('atom'      => 'Parenthesis'),
                                   0 => array('token'     => ArgumentsNoComma::$operators,
                                              'atom'      => 'none'),
                                   1 => array('atom'      => Arguments::$arguments),
                                   2 => array('token'     => 'T_CLOSE_PARENTHESIS',
                                              'atom'      => 'none')
        );

        $this->actions = array('insertVertex' => true,
                               'rank'         => array(1 => 0)
                               );
        $this->checkAuto();

        // @note function &f(1) : no comma
        $this->conditions = array(-2 => array('token'     => 'T_FUNCTION'),
                                  -1 => array('token'     => 'T_AND'),
                                   0 => array('token'     => ArgumentsNoComma::$operators,
                                              'atom'      => 'none'),
                                   1 => array('atom'      => Arguments::$arguments),
                                   2 => array('token'     => 'T_CLOSE_PARENTHESIS',
                                              'atom'      => 'none'),
                                   3 => array('filterOut' => 'T_DOUBLE_COLON'),
        );

        $this->actions = array('insertVertex' => true,
                               'rank'         => array(1 => 0)
                               );
        $this->checkAuto();

       // @note f[1](2) : no comma
        $this->conditions = array(-1 => array('atom'      => 'Array'),
                                   0 => array('token'     => ArgumentsNoComma::$operators,
                                              'atom'      => 'none'),
                                   1 => array('atom'      => Arguments::$arguments),
                                   2 => array('token'     => 'T_CLOSE_PARENTHESIS',
                                              'atom'      => 'none'),
                                   3 => array('filterOut' => array('T_DOUBLE_COLON', 'T_OPEN_PARENTHESIS')),
        );

        $this->actions = array('insertVertex' => true,
                               'rank'         => array(1 => 0)
                               );
        $this->checkAuto();

        // @note a->{f}(1) : no comma
        $this->conditions = array(-4 => array('token'     => array('T_OBJECT_OPERATOR', 'T_DOUBLE_COLON')),
                                  -3 => array('token'     => 'T_OPEN_CURLY'),
                                  -2 => array('atom'      => 'yes'),
                                  -1 => array('token'     => 'T_CLOSE_CURLY'),
                                   0 => array('token'     => ArgumentsNoComma::$operators,
                                              'atom'      => 'none'),
                                   1 => array('atom'      => Arguments::$arguments),
                                   2 => array('token'     => 'T_CLOSE_PARENTHESIS',
                                              'atom'      => 'none'),
                                   3 => array('filterOut' => array('T_DOUBLE_COLON', 'T_OPEN_PARENTHESIS')),
        );

        $this->actions = array('insertVertex' => true,
                               'rank'         => array(1 => 0));
        $this->checkAuto();

        return false;
    }
}
?>
