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


namespace Analyzer\Security;

use Analyzer;

class ShouldUsePreparedStatement extends Analyzer\Analyzer {
    public function analyze() {
        $functions = array( '\\pg_query',
                            '\\sqlsrv_query',
                            '\\cubrid_query',
                            '\\sqlite_query',
                            '\\sybase_query',
                            '\\ingres_query',
                            '\\pg_send_query',
                            '\\msql_db_query',
                            '\\mysql_db_query',
                            '\\fbsql_db_query',
                            '\\pg_cancel_query',
                            '\\ifx_query',
                            '\\ibase_free_query',
                            '\\dbx_query',
                            '\\maxdb_multi_query',
                            '\\sqlite_array_query',
                            '\\mysqli_slave_query',
                            '\\mysqli_master_query',
                            '\\sqlite_single_query');
        
        // dynamic type in the code : mysql_query($res, "select ".$a." from table");
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath($functions)
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->is('rank', 1)
             ->atomIs('Concatenation')
             ->back('first');
        $this->prepareQuery();

        // dynamic type in the code : mysql_query($res, "select $a from table");
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath($functions)
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->is('rank', 1)
             ->atomIs('String')
             ->outIs('CONTAINS')
             ->back('first');
        $this->prepareQuery();

        // dynamic type in the code : mysql_query($res, <<<HEREDOC select $a from table HEREDOC);
        $this->atomIs('Functioncall')
             ->hasNoIn('METHOD')
             ->tokenIs(array('T_STRING', 'T_NS_SEPARATOR'))
             ->fullnspath($functions)
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->is('rank', 1)
             ->atomIs('Heredoc')
             ->outIs('CONTAINS')
             ->is('heredoc', true)
             ->back('first');
        $this->prepareQuery();

        // method call $someObject->query('select '. 'b') (probably too wide...)
        $this->atomIs('Methodcall')
             ->outIs('METHOD')
             ->code('query')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->is('rank', 0)
             ->atomIs('Concatenation')
             ->back('first');
        $this->prepareQuery();

        // method call $someObject->query("select $b") (probably too wide...)
        $this->atomIs('Methodcall')
             ->outIs('METHOD')
             ->code('query')
             ->outIs('ARGUMENTS')
             ->outIs('ARGUMENT')
             ->is('rank', 0)
             ->atomIs('String')
             ->outIs('CONTAINS')
             ->back('first');
        $this->prepareQuery();
    }
}

?>
