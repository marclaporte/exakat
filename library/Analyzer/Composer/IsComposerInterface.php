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


namespace Analyzer\Composer;

use Analyzer;

class IsComposerInterface extends Analyzer\Analyzer {

    public function dependsOn() {
        return array('Interfaces/InterfaceUsage');
    }
    
    public function analyze() {
        $data = new \Data\Composer();

        $interfaces = $data->getComposerInterfaces();
        $interfacesFullNP = $this->makeFullNsPath($interfaces);
        
        $this->atomIs('Class')
             ->outIs('IMPLEMENTS')
             ->fullnspath($interfacesFullNP);
        $this->prepareQuery();

        $this->atomIs('Interface')
             ->outIs('EXTENDS')
             ->fullnspath($interfacesFullNP);
        $this->prepareQuery();

        $this->atomIs('Instanceof')
             ->outIs('CLASS')
             ->fullnspath($interfacesFullNP);
        $this->prepareQuery();

        $this->atomIs('Typehint')
             ->outIs('CLASS')
             ->fullnspath($interfacesFullNP);
        $this->prepareQuery();

        $this->atomIs('Use')
             ->outIs('USE')
             ->fullnspath($interfacesFullNP);
        $this->prepareQuery();
    }
}

?>
