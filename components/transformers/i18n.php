<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$

/**
* A translator, which tries to implement the i18n transformer from cocoon.
*
* See http://cocoon.apache.org/2.1/userdocs/transformers/i18n-transformer.html
*  for an introduction.
*
* If you want to use it, add the following to your sitemap
*
*  <map:transform type="i18n" src="xml/catalog">
*     <map:parameter name="locale" value="{lang}"/>
*     <map:parameter name="driver" value="xml"/>
*  </map:transform>
*
* There are (or will be) different drivers for getting the values,
*  currently only a xml driver is available. See the source comments
*  for more details.
*
* A DB driver is planned.
*  
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class popoon_components_transformers_i18n extends popoon_components_transformer  {
    
    public $XmlFormat = 'DomDocument';
    
    public $name = 'i18n';
    
    function __construct ($sitemap) {        
         parent::__construct($sitemap);
    }
    
    function init($attribs) {
        parent::init($attribs);
    }
    
    function DomStart(&$xml) {
    
        
        $src = $this->getAttrib("src");
        $lang = $this->getParameterDefault("locale");
        $driver = "popoon_components_transformers_i18n_".$this->getParameterDefault("driver");
        $d = new  $driver($src, $lang);       

        

        $ctx = new domxpath($xml);
        $ctx->registerNamespace("i18n","http://apache.org/cocoon/i18n/2.1");
        $res = $ctx->query("//i18n:text");

        foreach($res as $text) {
            if (!$locText = $d->getText($text->nodeValue)) {
                $locText = $text->nodeValue;
            }
            $text->parentNode->replaceChild($xml->createTextNode( $locText),$text);;
        }
    }
} 

?>
