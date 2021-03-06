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

class _Var extends TokenAuto {
    static public $operators = array('T_VAR');
    static public $atom = 'Var';

    public function _check() {
    // class x { var $x }
    // class x { var $x = 2 }
    // class x { var $x, $y }
        $allowedAtoms = array('Assignation', 'Variable');
        $this->conditions = array( 0 => array('token'    => self::$operators,
                                              'checkFor' => $allowedAtoms),
                                   1 => array('atom'     => $allowedAtoms),
                                   2 => array('token'    => array('T_SEMICOLON', 'T_COMMA')),
                                 );
        
        $this->actions = array('makePpp' => true,
                               'atom'    => 'Visibility',
                               );

        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

s=[];
fullcode.out('DEFINE').sort{it.rank}._().each{ s.add(it.fullcode);}
fullcode.setProperty('fullcode', 'var ' + s.join(', '));

fullcode.out('DEFINE').each{
    if (it.atom == 'Variable') {
        it.setProperty('propertyname', it.code.substring(1, it.code.size()).toLowerCase());
    } else if (it.atom == 'Assignation') {
        it.setProperty('propertyname', it.out('LEFT').next().code.substring(1, it.out('LEFT').next().code.size()).toLowerCase());
    }
}

GREMLIN;
    }
}
?>
