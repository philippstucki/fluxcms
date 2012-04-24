<?php

/**
 * Section base class
 *
 * @package bx_dbforms2
 * @author Philipp Stucki
 */
class bx_dbforms2_section {

    protected $XMLName = 'section';
    public $name = '';
    public $descr = '';
    
    /**
     *  Constructor
     *
     *  @param  type  $var descr
     *  @access public
     */
    public function __construct($name, $descr) {
        $this->name = $name;
        $this->descr = $descr;
    }
    
    /**
     *  Serializes the section to a DOM node.
     *
     *  @param  object $dom DOM object to be used to generate the node.
     *  @access public
     *  @return object DOM node
     */
    public function serializeToDOMNode($dom) {
        $node = $dom->createElement($this->XMLName);
        $node->setAttribute('name', $this->name);
        $node->setAttribute('descr', $this->descr);
        return $node;
    }
    
}

