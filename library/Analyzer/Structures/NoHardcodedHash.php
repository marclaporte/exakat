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
namespace Analyzer\Structures;

use Analyzer;

class NoHardcodedHash extends Analyzer\Analyzer {
    public function analyze() {
        $algos = $this->loadJson('hash_length.json');
        
        // Find common hashes, based on hexadecimal and length
        foreach($algos as $size => $algo) {
            $this->atomIs('String')
                 ->tokenIsNot('T_QUOTE')
                 ->regex('noDelimiter', '^[a-fA-Z0-9]{'.$size.'}\\$')
                 ->isNotMixedcase('noDelimiter');
            $this->prepareQuery();
        }

        // Crypt (some salts are missing)
        $this->atomIs('String')
             ->tokenIsNot('T_QUOTE')
             ->regex('noDelimiter', '^\\\\\\$(1|2a|2x|2y|5|6)\\\\\\$.+');
        $this->prepareQuery();

        // Base64 encode
        $this->atomIs('String')
             ->tokenIsNot('T_QUOTE')
             ->regex('noDelimiter', '^[a-zA-Z0-9]+={0,2}\\$')
              ->filter('(it.noDelimiter.length() % 4) == 0')
              ->codeLength(' > 101');
        $this->prepareQuery();
    }
}

?>
