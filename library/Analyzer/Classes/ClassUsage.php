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


namespace Analyzer\Classes;

use Analyzer;

class ClassUsage extends Analyzer\Analyzer {

    public function analyze() {
        $this->atomIs('New')
             ->outIs('NEW');
        $this->prepareQuery();
        
        $this->atomIs('Staticmethodcall')
             ->outIs('CLASS');
        $this->prepareQuery();

        $this->atomIs('Staticproperty')
             ->outIs('CLASS');
        $this->prepareQuery();

        $this->atomIs('Staticconstant')
             ->outIs('CLASS');
        $this->prepareQuery();

        $this->atomIs('Catch')
             ->outIs('CLASS');
        $this->prepareQuery();

        $this->atomIs('Typehint')
             ->outIs('CLASS');
        $this->prepareQuery();

        $this->atomIs('Instanceof')
             ->outIs('CLASS');
        $this->prepareQuery();

        $this->atomIs('Class')
             ->outIs(array('EXTENDS', 'IMPLEMENTS'));
        $this->prepareQuery();

        $this->atomIs('Use')
             ->outIs('USE');
        $this->prepareQuery();

        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath('\\class_alias')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->is('rank', 0)
             ->atomIs('String');
        $this->prepareQuery();
    }
}

?>
