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
// $Id: xml.php,v 1.19 2004/02/23 23:47:33 chregu Exp $

/**
* Outputs the XML-Document as XML
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xml.php,v 1.19 2004/02/23 23:47:33 chregu Exp $
* @package  popoon
*/
class popoon_components_serializers_xml extends popoon_components_serializer {

	var $XmlFormat = "Own";
    var $contentType = "text/xml";
    
	function __construct (&$sitemap) {
		$this->sitemap = &$sitemap;
	}

    function init($attribs) {
        parent::init($attribs);
    }
	
    function DomStart(&$xml)
    {
        parent::DomStart($xml);
    	if (is_object($xml))
        {
			$this->sitemap->hasFinalDom = true;
			print str_replace("HTML","html",$xml->saveXML());
		}
        else
        {	
        	print $xml;
		}
	}
}


?>
