<?php
/*
 * Copyright 2012-2015 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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


namespace Analyzer\Variables;

use Analyzer;

class OverwrittenLiterals extends Analyzer\Analyzer {
    public function dependsOn() {
        return array('Analyzer\\Variables\\IsModified');
    }
    
    public function analyze() {
        $this->atomIs('Assignation')
             ->code('=')
             ->hasNoIn('INIT')
             ->outIs('RIGHT')
             ->atomIs(array('Integer', 'String', 'Real', 'Float'))
             ->inIs('RIGHT')
             ->outIs('LEFT')
             ->atomIs('Variable')
             ->analyzerIsNot('self')
             ->_as('result')
             ->analyzerIs('Analyzer\\Variables\\IsModified')
             ->fetchContext()
             ->eachCounted('it.code + "/" + context.Function + "/" + context.Class + "/" + context.Namespace', 1, '>');
        $this->prepareQuery();
    }
}

?>