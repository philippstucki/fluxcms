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

include_once("popoon/components/serializer.php");

/**
* Documentation is missing at the moment...
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class popoon_components_serializers_xhtml extends popoon_components_serializer {

    public $XmlFormat = "Own";
    public $contentType = "text/html";

    function __construct (&$sitemap) {
        $this->sitemap = &$sitemap;
    }
    
    function init($attribs) {
        parent::init($attribs);
    }

    function DomStart(&$xml)
    {
        parent::DomStart($xml);
        
        if (is_object($xml)) {
            $this->sitemap->hasFinalDom = false;
            $xml = $xml->saveXML();
        }
        if ($errhandler = $this->getParameterDefault("outputErrors")) {
            $err = $this->getErrorReporting($errhandler);
            if ($err) {
                $xml = str_replace("</html>",$err."</html>",$xml);
            }
        }
        print $this->cleanXHTML($xml);
        
    }
        
    function cleanXHTML($xml) {
        /* for some strange reasons, libxml makes an upercase HTML, which the w3c validator doesn't like */
        if ($this->getParameterDefault("stripScriptCDATA") == "true") {
            $xml = $this->stripScriptCDATA($xml);
        }
        return $this->obfuscateMail(str_replace("DOCTYPE HTML","DOCTYPE html",$xml));
    }

    function stripScriptCDATA($xml) {
        $xml = preg_replace("#(<script[^>]+>)<!\[CDATA\[#","$1",$xml);
        return preg_replace("#\]\]>(</script>)#","$1",$xml);
    }
        
    function obfuscateMail($xml) {
	 if ($this->getParameter('default','obfuscateMail') == 'true') {
                return str_replace('mailto:','&#109;&#97;&#105;&#108;&#116;&#111;&#58;',str_replace('@','&#64;',$xml));
        }
	return $xml;
    }
    
    function getErrorReporting($class) {
        eval('$err = '.$class.'::getInstance();');
        if ($err->hasErrors()) {
            return $err->getHtml();
        } else {
            return null;
        }
        restore_error_handler();
    }
	
    

        
}


?>
