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
class bx_dbforms2_fields_relation_n2m extends bx_dbforms2_field {

    public function __construct($name) {
        parent::__construct($name);
        $this->XMLName ='select';
        $this->type ='relation_n2m';
    }

    /**
     *  Returns an array containing all attributes which the field can handle.
     *
     *  @access public
     *  @return array Field attributes
     */
    public function getConfigAttributes() {
        $ret = parent::getConfigAttributes();
        $ret['relationtable'] = 'string';
        $ret['thisidfield'] = 'string';
        $ret['thatidfield'] = 'string';
        $ret['linktothat'] = 'string';
        $ret['indexfield'] = 'string';
        return $ret;
    }
    
    public function hasConfigValues() {
        return true;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getAdditionalData($id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
        
        $indexField = $db->quoteIdentifier($this->attributes['indexfield']);

        $query ='SELECT '. $this->attributes['thatidfield'] . ' AS _idfield FROM '. $tablePrefix.$this->attributes['relationtable'] .' WHERE ' . $this->attributes['thisidfield'] .' = '.$db->quote($id)." ORDER BY {$indexField}";
        $res = $db->query($query);
        $v = array();
        if (!MDB2::isError($res)) {
            while ($row= $res->fetchRow(MDB2_FETCHMODE_ORDERED)) {
                $v[$row[0]] = $this->values[$row[0]];
            }
        }
        return $v;
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function getSQLName() {
        return null;  
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    public function doAdditionalQuery($id) {
        if ($this->parentForm->queryMode == bx_dbforms2::QUERYMODE_DELETE) {
            return  $this->_deleteQuery($id);
        } else {
            return $this->_updateInsertQuery($id);
        }
        
        
    }
    
    /**
     *  DOCUMENT_ME
     *
     *  @param  type  $var descr
     *  @access public
     *  @return type descr
     */
    protected function _deleteQuery($id) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->dbwrite;
         $query = "DELETE FROM ".$tablePrefix.$this->attributes['relationtable'] .' WHERE '. $this->attributes['thisidfield'] . ' = '. $id ;
         $res = $db->query($query);
         return null;
    }


    protected function _updateInsertQuery( $id ) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->dbwrite;
        $ids = $this->value;

        $query = 'select ' . $this->attributes['thatidfield'] .' from '.$tablePrefix.$this->attributes['relationtable'].' where ' . $this->attributes['thisidfield'] .' = '. $id;
        $res = $db->query($query);
        $current = $res->fetchCol();

        $todelete = array_diff( $current , $ids );
        $toinsert = array_diff( $ids , $current );
        $toupdate = array_intersect( $ids , $current );
        
        $indexField = $db->quoteIdentifier($this->attributes['indexfield']);

        // delete entries which are not associated anymore
        if( !empty( $todelete ) ) {
            $query = 'DELETE FROM '.$tablePrefix.$this->attributes['relationtable'] .' WHERE ' . $this->attributes['thisidfield'] .' = '. $db->quote($id) . ' AND ' . $this->attributes['thatidfield'] ." IN ('" . implode( "','" , $todelete)."' ) ";
            $res = $db->query($query);
        }

        // insert new assocations
        foreach( $toinsert as $i => $value ) {
            $seqid = $db->nextID( $GLOBALS['POOL']->config->getTablePrefix() . "_sequences" );
            $query = 'INSERT INTO '.$tablePrefix.$this->attributes['relationtable']  .'(id, '. $this->attributes['thisidfield'] . ' , '. $this->attributes['thatidfield'] . " , {$indexField} ) VALUES ({$seqid}," . $db->quote($id) . "," . $db->quote($value).",". $db->quote($i) ." )";
            $res = $GLOBALS['POOL']->db->query($query);
        }

        // update order on existing associations
        foreach( $toupdate as $i => $uid ) {
            $query = "UPDATE ".$tablePrefix.$this->attributes['relationtable'];
            $query.= " SET {$indexField}=".$db->quote($i);
            $query.= " WHERE ".$this->attributes['thisidfield'].' = '.$db->quote($id);
            $query.= " AND ".$this->attributes['thatidfield'].' = '.$db->quote($uid);
            $res = $GLOBALS['POOL']->db->query($query);
        }
    }
    
    protected function getXMLAttributes() {
        if (empty($this->attributes['linktothat'])) {
               return array('linktothat' => "");
        } else {
            return array('linktothat' => $this->attributes['linktothat']);
        }
    }
   
}

?>
