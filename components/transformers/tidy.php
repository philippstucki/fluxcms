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

include_once("popoon/components/transformer.php");

/**
* Transforms an XML-Document with the help of libxslt out of domxml
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class popoon_components_transformers_tidy extends popoon_components_transformer {

    public $XmlFormat = "XmlString";
	public $classname = "tidy";
    
    
    function __construct(&$sitemap) {
        parent::__construct($sitemap);
    }

    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        // default properties
        // can be overwritten from the sitemap with for example
        //  <map:parameter name="wrap" value="80"/>
        
        $options = array(
            "output-xhtml" => true, 
            "clean" => true, 
            "wrap" => "350", 
            "indent" => true, 
            "indent-spaces" => 1,
            "ascii-chars" => "no",
            "char-encoding" => "utf8",
            "wrap-attributes" => false,
            "alt-text" => "none",
            "doctype" => "loose",
            "numeric-entities" => true,
	     "drop-proprietary-attributes" => true
            );
        
        $options = array_merge($options,$this->getParameter("default") );
        
        $tidy = new tidy();
        
        if(!$tidy) {
            throw new Exception("Something went wrong with tidy initialisation");
        }
        $tidy->parseString($xml,$options,$options["char-encoding"]);
        $tidy->cleanRepair();
        $xml = (string) $tidy;
        if (isset($options['remove-xmlns']) && $options['remove-xmlns']) {
            $xml = preg_replace('/xmlns="[^"]*"/','',$xml);
        }
        
    }
}


?>
