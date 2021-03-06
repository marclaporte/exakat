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

class _String extends TokenAuto {
    static public $operators = array('T_QUOTE');
    static public $allowedClasses = array('String', 'Variable', 'Concatenation', 'Array', 'Property', 'Methodcall',
                                          'Staticmethodcall', 'Staticproperty', 'Staticconstant', 'Ternary', 'Concatenation',
                                          'Functioncall');
    static public $atom = 'String';

    public function _check() {
// Case of string with interpolation : "a${b}c";
        $this->conditions = array(  0 => array('token'          => self::$operators,
                                               'atom'           => 'none'),
                                    1 => array('atom'           => self::$allowedClasses,
                                               'checkForString' => self::$allowedClasses),
                                 );

        $this->actions = array( 'makeQuotedString' => '_String');
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

s = [];
fullcode.out('CONCAT').sort{it.rank}._().each{ s.add(it.fullcode); }
fullcode.setProperty('fullcode', '"' + s.join('') + '"');
fullcode.setProperty('count', s.size());

if (fullcode.code.length() > 1) {
    if (fullcode.code.substring(0, 1) in ["'", '"']) {
    // @note : only the first delimiter is removed, it is sufficient
        fullcode.setProperty("delimiter", fullcode.code.substring(0, 1));
        fullcode.setProperty("noDelimiter", fullcode.code.substring(1, fullcode.code.length() - 1));
    }
}

GREMLIN;
    }
}

?>
