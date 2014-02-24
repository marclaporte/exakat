<?php

namespace Tokenizer;

class _Class extends TokenAuto {
    static public $operators = array('T_CLASS');

    function _check() {
    
    // class x {}
        $this->conditions = array( 0 => array('token' => _Class::$operators),
                                   1 => array('atom' => 'Identifier')
                                 );
        
        $this->actions = array('transform'   => array(   1 => 'NAME'),
                               'atom'        => 'Class_tmp',
                               'keepIndexed' => true);
        $this->checkAuto(); 

    // class x extends y {}
        $this->conditions = array( 0 => array('token' => _Class::$operators, 'atom' => 'Class_tmp'),
                                   1 => array('token' => 'T_EXTENDS'),
                                   2 => array('atom'  => array('Identifier', 'Nsname')),
                                   3 => array('filterOut2' => 'T_NS_SEPARATOR'),
                                 );
        
        $this->actions = array('transform'   => array( 1 => 'DROP',
                                                       2 => 'EXTENDS'),
                               'keepIndexed' => true
                               );
        $this->checkAuto(); 

    // class x implements a {}
        $this->conditions = array( 0 => array('token'     => _Class::$operators, 'atom' => 'Class_tmp'),
                                   1 => array('token'     => 'T_IMPLEMENTS'),
                                   2 => array('atom'      => array('Identifier', 'Nsname')),
                                   3 => array('filterOut' => array('T_COMMA', 'T_NS_SEPARATOR'))
                                 );
        
        $this->actions = array('transform'   => array( 1 => 'DROP',
                                                       2 => 'IMPLEMENTS'),
                               'keepIndexed' => true );
        $this->checkAuto(); 

    // class x implements a,b,c {}
        $this->conditions = array( 0 => array('token' => _Class::$operators, 'atom' => 'Class_tmp'),
                                   1 => array('token' => 'T_IMPLEMENTS'),
                                   2 => array('atom' => 'Arguments'),
                                   3 => array('filterOut' => array('T_COMMA')),
                                 );
        
        $this->actions = array('transform'   => array( 1 => 'DROP',
                                                       2 => 'TO_IMPLEMENTS'),
                               'keepIndexed' => true );
        $this->checkAuto(); 

    // class x { // some real code}
        $this->conditions = array( 0 => array('token' => _Class::$operators, 'atom' => 'Class_tmp'),
                                   1 => array('atom' => 'Block')
                                 );
        
        $this->actions = array('transform'   => array(1 => 'BLOCK'),
                               'atom'       => 'Class',
                               'cleanIndex' => true);
        $this->checkAuto(); 

        return $this->checkRemaining();
    }

    function fullcode() {
        return 'it.fullcode = "class " + it.out("NAME").next().code; 
current = it;

// extends
it.out("EXTENDS").each{ current.fullcode = current.fullcode + " extends " + it.fullcode;}

// implements
if (it.out("IMPLEMENTS").count() > 0) {
    i = [];
    it.out("IMPLEMENTS").each{ i.add(it.fullcode); }
    current.fullcode = current.fullcode + " implements " + i.join(", ");
}
        
        ';
// didn't added code, it seems too much....
    }

}
?>