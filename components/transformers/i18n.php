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
         if (!defined('I18NNS')) {
             define('I18NNS', 'http://apache.org/cocoon/i18n/2.1');
         }
    }
    
    function init($attribs) {
        parent::init($attribs);
    }
    
    function DomStart(&$xml) {
    
        
        $src = $this->getAttrib("src");
        $lang = $this->getParameterDefault("locale");
        setlocale(LC_ALL,$lang);
        $driver = "popoon_components_transformers_i18n_".$this->getParameterDefault("driver");
        $d = new  $driver($src, $lang);       

        $ctx = new domxpath($xml);
        $ctx->registerNamespace("i18n",I18NNS);
        
        //translate i18n:text 
        $res = $ctx->query("//i18n:text");

        foreach($res as $text) {
            if ($text->hasAttributeNS(I18NNS,"key")) {
                $key = $text->getAttributeNS(I18NNS,"key");   
            } else {
                $key = $text->nodeValue;
            }                
            if (!$locText = $d->getText($key)) {
                $locText = $key;
            }
            $text->parentNode->replaceChild($xml->createTextNode( $locText),$text);
        }
        
        // translate i18n:attr
        $res = $ctx->query("//@i18n:attr");
        foreach($res as $node) {
            foreach (explode(" ",$node->value) as $attrName) {
                if ($key = $node->parentNode->getAttribute($attrName)) {
                      if (!$locText = $d->getText($key)) {
                          $locText = $key;
                      } 
                      $node->parentNode->setAttribute($attrName,$locText);
                }
            }
            $node->parentNode->removeAttributeNode($node);
        }
        
        // i18n:number
        
        /* <i18n:number type="int-currency-no-unit" value="170374" />
           <i18n:number type="int-currency" value="170374" />
           and
           <i18n:number type="percent" value="1.2" />
           are not supported yet
           */
           $res = $ctx->query("//i18n:number");
           foreach($res as $node) {
               switch ($node->getAttribute("type")) {
                   case "currency":
                   if ($digits = $node->getAttribute("fraction-digits")) {
                       $value = money_format("%.${digits}n",$node->getAttribute("value"));
                   } else {
                       $value = money_format("%n",$node->getAttribute("value"));
                   }
                   break;
                   case "printf":
                   $value = sprintf($node->getAttribute("pattern"),$node->getAttribute("value"));
               }   
               $node->parentNode->replaceChild($xml->createTextNode($value),$node);
               
           }
           
           //i18n:date-time
           /* only i18n:date-time is supported right now
                it uses the strftime format of PHP not the java date format, eg.
                pattern should be "%d:%b:%Y" and not "dd:MMM:yyyy".
                
              short/medium/long/full are also not implemented. Use
              %c, %x and %X as an alternative.
           */
           $res = $ctx->query("//i18n:date-time");
           foreach($res as $node) {
                $pattern = $node->getAttribute("pattern");
                $src = $node->getAttribute("src-pattern");
                $value = $node->getAttribute("value");
                if (!$value) {
                    $value = time();
                }
                else if ($src && function_exists("strptime")) {
                    $t = strptime($value,$src);
                    $value = mktime($t['tm_hour'],$t['tm_min'],$t['tm_sec'],$t['tm_mon'],$t['tm_mday'],$t['tm_year']);
                } else {
                    $value = strtotime($value);
                }
                $value = strftime($pattern,$value);
                $node->parentNode->replaceChild($xml->createTextNode($value),$node);
                    
           }
    }
} 

?>
