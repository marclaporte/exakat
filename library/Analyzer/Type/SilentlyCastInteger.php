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

class SilentlyCastInteger extends Analyzer\Analyzer {
    public function analyze() {
        // Binary or hexadecimal, cast to Float
        $this->atomIs('Float')
             ->regex('code', '0[xXbB]')
             ->back('first');
        $this->prepareQuery();

        // Octal cast to Float
        $this->atomIs('Float')
             ->regex('code', '^0')
             ->regexNot('code', '\\\\.')
             ->back('first');
        $this->prepareQuery();

        // Too long integer
        $this->atomIs('Float')
             ->regex('code', '^[0-9]+\\$')
             ->regexNot('code', '\\\\.')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
