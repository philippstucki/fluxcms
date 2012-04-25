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
// $Id: text.php 8653 2007-05-11 13:54:05Z chregu $

/**
 * DOCUMENT_ME
 *
 * @package bx_dbforms2
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */

 class bx_dbforms2_fields_time extends bx_dbforms2_field {
    
    public function __construct($name) {
        parent::__construct($name);
        $this->type = 'time';
        $this->XMLName = 'input';
    }
    
    public function getConfigAttributes() {
        $ret =  parent::getConfigAttributes();
        $ret['nullonzero'] = 'boolean';
        return $ret;
    }


    public function getSQLValue() {
        if(
            $this->attributes['nullonzero'] === TRUE
            && empty($this->value) 
            && (
                $this->parentForm->queryMode == bx_dbforms2::QUERYMODE_INSERT 
                || $this->parentForm->queryMode == bx_dbforms2::QUERYMODE_UPDATE
            )
         ) {
            return 'NULL';
        } else {
            return $this->value;
        }
    }
    
    public function quoteSQLValue() {
        if( empty($this->value) && (
            $this->parentForm->queryMode == bx_dbforms2::QUERYMODE_INSERT 
            || $this->parentForm->queryMode == bx_dbforms2::QUERYMODE_UPDATE)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
}

