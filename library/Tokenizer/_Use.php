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

class _Use extends TokenAuto {
    static public $operators = array('T_USE');
    static public $atom = 'Use';

    public function _check() {

    // use \a\b\{} (grouping)
        $this->conditions = array( 0 => array('token' => self::$operators),
                                   1 => array('atom'  => array('Identifier', 'Nsname')),
                                   2 => array('token' => 'T_NS_SEPARATOR'),
                                   3 => array('token' => 'T_OPEN_CURLY'),
                                   4 => array('token' => array('T_STRING', 'T_AS', 'T_NS_SEPARATOR', 'T_CONST', 'T_FUNCTION')),
//                                   4 => array('atom'  => array('Identifier', 'As', 'Nsname')),
//                                   5 => array('token' => array('T_COMMA', 'T_CLOSE_CURLY')),
                                 );
        
        $this->actions = array('makeGroupedUse' => true,
                               'atom'           => 'Use'
                               );
        $this->checkAuto();

    // use \a\b; (Finished)
        $this->conditions = array( 0 => array('token'    => self::$operators,
                                              'checkFor' => array('Identifier', 'Nsname', 'As')),
                                   1 => array('atom'     => array('Identifier', 'Nsname', 'As')),
                                   2 => array('token'    => array('T_SEMICOLON', 'T_INLINE_HTML'))
                                 );
        
        $this->actions = array('makeFromList'       => 'USE',
                               'atom'               => 'Use',
                               'cleanIndex'         => true,
                               'addSemicolon'       => 'it'
                               );
        $this->checkAuto();

    // use \a\b { 
        $this->conditions = array( 0 => array('token'    => self::$operators,
                                              'checkFor' => array('Identifier', 'Nsname', 'As')),
                                   1 => array('atom'     => array('Identifier', 'Nsname', 'As')),
                                   2 => array('token'    => array('T_COMMA', 'T_OPEN_CURLY'))
                                 );
        
        $this->actions = array('makeFromList'       => 'USE',
                               'atom'               => 'Use',
                               'keepIndexed'        => true,
                               'cleanIndex'         => true
                               );
        $this->checkAuto();

    // use \a\b {} (Not grouping, detailing imported methods/constants)
        $this->conditions = array( 0 => array('token'     => self::$operators,
                                              'atom'      => 'yes'),
                                   1 => array('token'     => 'T_OPEN_CURLY'),
                                   2 => array('atom'      => array('Sequence', 'Void')),
                                   3 => array('token'     => 'T_CLOSE_CURLY'),
                                 );
        
        $this->actions = array('transform'          => array(1 => 'DROP',
                                                             2 => 'BLOCK',
                                                             3 => 'DROP'),
                               'addAlwaysSemicolon' => 'it',
                               'makeBlock'          => 'BLOCK',
                               'cleanIndex'         => true
                               );
        $this->checkAuto();

    // use function|const \a\b\ {}; with grouping
        $this->conditions = array(  0 => array('token' => self::$operators),
                                    1 => array('token' => array('T_FUNCTION', 'T_CONST')),
                                    2 => array('atom'  => array('Identifier', 'Nsname')),
                                    3 => array('token' => 'T_NS_SEPARATOR'),
                                    4 => array('token' => 'T_OPEN_CURLY'),
                                    5 => array('atom'  => array('Identifier', 'Nsname', 'As')),
                                    6 => array('token' => array('T_COMMA', 'T_CLOSE_CURLY')),
                                 );
        
        $this->actions = array('makeGroupedUse' => true,
                               'atom'           => 'Use'
                               );
        $this->checkAuto();

    // use const|function \a\b;
        $this->conditions = array(  0 => array('token'        => self::$operators,
                                               'checkNextFor' => array('Identifier', 'Nsname', 'As')),
                                    1 => array('token'        => array('T_FUNCTION', 'T_CONST')),
                                    2 => array('atom'         => array('Identifier', 'Nsname', 'As')),
                                    3 => array('token'        => array('T_COMMA', 'T_OPEN_CURLY', 'T_SEMICOLON'))
                                 );
        
        $this->actions = array('makeNextFromList' => true,
                               'atom'             => 'Use',
                               'keepIndexed'      => true,
                               'cleanIndex'       => true,
                               );
        $this->checkAuto();

        return false;
    }

    public function fullcode() {
        return <<<GREMLIN

if (fullcode.groupedUse == true) {
    if (fullcode.groupedPrefix == null) {
        s = [];
        fullcode.out('USE', 'FUNCTION', 'CONST').sort{it.rank}._().each{
            a = it.getProperty('fullcode');
            link = it.inE().next().label.toLowerCase() + ' ';
            if (link == 'use ') { link = ''; }

            s.add(link + a);
        };

        fullcode.fullcode = fullcode.getProperty('code') + ' ' + fullcode.groupPath + "{ " + s.join(", ") + " }";
    } else {
        s = [];
        fullcode.out('USE', 'FUNCTION', 'CONST').sort{it.rank}._().each{
            s.add(it.getProperty('fullcode'));
        };
        
        fullcode.fullcode = fullcode.getProperty('code') + ' ' + fullcode.getProperty('groupedPrefix') + ' ' + fullcode.groupPath + "{ " + s.join(", ") + " }";
    }
} else {
    s = [];
    fullcode.out('USE', 'FUNCTION', 'CONST').sort{it.rank}._().each{
        s.add(it.getProperty('fullcode'));
    };

    if (fullcode.out('FUNCTION').any()) {
        fullcode.setProperty('fullcode', fullcode.getProperty('code') + " function ");
        fullcode.setProperty('originpath', fullcode.getProperty('code').toLowerCase());
    } else if (it.out('CONST').any()) {
        fullcode.setProperty('fullcode', fullcode.getProperty('code') + " const ");
        fullcode.setProperty('originpath', fullcode.getProperty('code').toLowerCase());
    } else {
        // Normal use
        fullcode.setProperty('fullcode', fullcode.getProperty('code') + " ");
    }

    fullcode.fullcode = fullcode.fullcode + s.join(", ");
}


// use a (aka c);
fullcode.out('USE').has('atom', 'Identifier').each{
    it.setProperty('originpath', it.code.toLowerCase());
    it.setProperty('originclass', it.code);
    
    it.setProperty('alias', it.code.toLowerCase());
    it.setProperty('originalias', it.code);
}

// use a\b\c as c (aka c);
fullcode.out('USE').has('atom', 'As').each{
    it.setProperty('alias', it.out('AS').next().code.toLowerCase());
    it.setProperty('originalias', it.out('AS').next().code);

    it.setProperty('originpath', it.out('NAME').next().fullcode.toLowerCase());
    it.setProperty('originclass', it.sideEffect{last = it.out('NAME').count() - 1}.out('NAME').filter{ it.rank == last}.next().fullcode);
}

// use b\c\a; (aka a)
fullcode.out('USE').has('atom', 'Nsname').each{
    s = [];
    it.out("SUBNAME").sort{it.rank}._().each{
        last = it.getProperty('code');
        s.add(last);
    };

    if (it.absolutens == true) {
        it.setProperty('originpath', '\\\\' + s.join('\\\\').toLowerCase());
        it.setProperty('originclass', s[s.size() - 1]);
    } else {
        it.setProperty('originpath', s.join('\\\\').toLowerCase());
        it.setProperty('originclass', s[s.size() - 1]);
    }
    
    if (it.out('AS').any()) {
        alias = it.out('AS').next().code;
        it.setProperty('alias', alias.toLowerCase());
        it.setProperty('originalias', alias);
    } else {
        it.setProperty('alias', last.toLowerCase());
        it.setProperty('originalias', last);
    }
}

// use function a as b;
// use const a as b;
fullcode.out('FUNCTION', 'CONST').each{
    s = [];
    it.out("SUBNAME").sort{it.rank}._().each{
        s.add(it.getProperty('code'));
    };
    if (s.size() == 0) {
        s = [it.code];
    }
    if (it.absolutens == true) {
        it.setProperty('originpath', '\\\\' + s.join('\\\\').toLowerCase());
        it.setProperty('originclass', s[s.size() - 1]);
    } else {
        it.setProperty('originpath', s.join('\\\\').toLowerCase());
        it.setProperty('originclass', s[s.size() - 1]);
    }
    
    if (it.out('AS').any()) {
        it.setProperty('alias', it.out('AS').next().code.toLowerCase());
        it.setProperty('originalias', it.out('AS').next().code);
    } else {
        it.setProperty('alias', s[s.size() - 1].toLowerCase());
        it.setProperty('originalias', s[s.size() - 1]);
    }
}

GREMLIN;
    }

}
?>
