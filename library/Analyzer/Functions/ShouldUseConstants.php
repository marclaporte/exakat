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


namespace Analyzer\Functions;

use Analyzer;

class ShouldUseConstants extends Analyzer\Analyzer {
    public function analyze() {
        $functions = $this->loadIni('constant_usage.ini');
        
        $positions = array(0, 1, 2, 3, /*4, 5,*/ 6);
        foreach($positions as $position) {
            $this->atomFunctionIs($functions['functions' . $position])
                 ->outIs('ARGUMENTS')
                 ->outIs('ARGUMENT')
                 ->is('rank', $position)
                 ->atomIsNot(array('Logical', 'Variable', 'Array', 'Property', 'Identifier', 'Nsname', 'Staticproperty', 'Staticconstant', 'Staticmethodcall', 'Methodcall'))
                 ->back('first');
            $this->prepareQuery();

            $this->atomFunctionIs($functions['functions' . $position])
                 ->outIs('ARGUMENTS')
                 ->outIs('ARGUMENT')
                 ->is('rank', $position)
                 ->atomIs('Logical')
                 ->raw('filter{ it.out.loop(1){!(it.object.atom in ["Identifier", "Nsname"])}{!(it.object.atom in ["Identifier", "Nsname", "Parenthesis", "Logical"])}.any()}')
                 ->back('first');
            $this->prepareQuery();
        }
    }
}

?>
