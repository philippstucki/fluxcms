<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <contact@liip.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dbforms2_fields_text extends bx_dbforms2_field {
    
    public function __construct($name) {
        parent::__construct($name);
        $this->type = 'text';
        $this->XMLName = 'input';

        $this->attributes['size'] = 80;
        $this->attributes['maxlength'] = 255;
    }
    
    public function getConfigAttributes() {
        $ret =  parent::getConfigAttributes();
        $ret['size'] = 'int';
        $ret['maxlength'] = 'int';
        
        return $ret;
    }
    
    protected function getXMLAttributes() {
        return array(
            'size' => $this->attributes['size'],
            'maxlength' => $this->attributes['maxlength'],
        );
    }
    
}

