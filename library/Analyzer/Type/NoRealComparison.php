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


namespace Analyzer\Type;

use Analyzer;

class NoRealComparison extends Analyzer\Analyzer {
    public function analyze() {
        // 1.2 == 3.4
        $this->atomIs('Comparison')
             ->code(array('==', '!=', '===', '!=='))
             ->outIs(array('LEFT', 'RIGHT'))
             ->atomIs('Float')
             ->back('first');
        $this->prepareQuery();

        // 1.2 == 3.4 + 0
        $this->atomIs('Comparison')
             ->code(array('==', '!=', '===', '!=='))
             ->outIs(array('LEFT', 'RIGHT'))
             ->atomInside('Float')
             ->hasNoIn(array('ARGUMENT', 'INDEX'))
             ->back('first');
        $this->prepareQuery();

        // 1.2 == ( 2 / 3)
        $this->atomIs('Comparison')
             ->code(array('==', '!=', '===', '!=='))
             ->outIs(array('LEFT', 'RIGHT'))
             ->atomIs('Multiplication')
             ->code('/')
             ->back('first');
        $this->prepareQuery();

        // 1.2 == ( 2 / 3)
        $this->atomIs('Comparison')
             ->code(array('==', '!=', '===', '!=='))
             ->outIs(array('LEFT', 'RIGHT'))
             ->atomInside('Multiplication')
             ->code('/')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
