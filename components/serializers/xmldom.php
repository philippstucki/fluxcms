<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2005 Bitflux GmbH                                 |
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

include_once("popoon/components/serializer.php");

/**
* Outputs the XML-Document as XML
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class serializer_xmldom extends serializer {

	var $XmlFormat = "Own";
    var $sitemap = null;
    var $contentType = "text/xml";

	function serializer_xmldom (&$sitemap) {
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
	}
        else
        {	
        	print $xml;
		}
	}
}


?>
