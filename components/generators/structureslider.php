<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

include_once("popoon/components/generators/structure2xml.php");

/**
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class generator_structureslider extends generator_structure2xml {

    // do st2xmlCaching;
    var $st2xmlCaching = false;
    
    
    // If we want to have different options for saving the queries than    
    // $PageOptions, do them here
    var $queryCacheOptions = null;
    
    // holds the information about the different queries 
    var $queries = null;
    var $defaultExpires = 3600;
    var $db =false;
    var $dsn = "";

    function generator_structureslider (&$sitemap) {
        $this->generator($sitemap);
    }
    
    function init($attribs) {
        parent::init($attribs);
        if ($this->dsn) {
            $this->db= common::getDbFromDsn($this->dsn);
        }
        
    }
    
    function DomStart(&$xml)
    {
        $options = array();
        $searches = $this->getParameter("search");
        if (is_array($searches)) {
            foreach($searches as $section => $query)
            {
                $options[$section] = array("where" => $query);
            }
        }
        if ($postsPerPage = $this->getParameterDefault('postsPerPage')) {
            if (!$postsStart = $this->getParameterDefault('postsStart')) {
                $postsStart = 0;
            }
                
            $options[$this->getParameterDefault('postsSection')]['limit'] = "$postsStart, $postsPerPage";
        }
   
     
        
        $xmlObj = &$this->showPage($this->getAttrib("src"),$options, true);
        
        $this->queries['page']['query'] = str_replace("\n"," ",$this->queries['page']['query']);
        
        $query = preg_match('#select(.*)from(.*)where(.*)LIMIT(.*)#i', $this->queries['page']['query'],$match);
        $count['max'] = $this->db->getOne('select count(ID) as count from '.$match[2]. ' where ' . $match[3]);
        $count['postsPerPage'] = $postsPerPage;
        $count['postsStart'] = $postsStart;

        $xmlObj->setOptions(array("user_options"=>array("result_root"=>"querystring")),true);

        $querystring["query"] = "<![CDATA[".str_replace("&amp;","&",preg_replace("#&*(path|start)=[^\&]*(\&|$)#","",$_SERVER["QUERY_STRING"])). ']]>';
        $xmlObj->add('<?xml version="1.0" ?><querystring><query>'.$querystring["query"].'</query></querystring>');
       
       $xmlObj->setOptions(array("user_options"=>array("result_root"=>"slider")),true);
        $xmlObj->add($count);
        $xml = $xmlObj->getXmlObject();
        return True;
    }
    

    
}
?>
