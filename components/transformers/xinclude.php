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
// $Id: xinclude.php,v 1.2 2004/02/23 23:47:33 chregu Exp $

include_once("popoon/components/transformer.php");

/**
* Transforms an XML-Document with the help of libxslt out of domxml
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: xinclude.php,v 1.2 2004/02/23 23:47:33 chregu Exp $
* @package  popoon
*/
class transformer_xinclude extends transformer {

    var $XmlFormat = "DomDocument";
	var $classname = "xinclude";
    function transformer_libxslt (&$sitemap) {

		$this->transformer($sitemap);
    }

    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        $xml->xinclude();
    }
}


?>
