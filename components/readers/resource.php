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

include_once("popoon/components/reader.php");

/**
 * @author   Christian Stocker <chregu@bitflux.ch>
 * @version  $Id$
 * @package  popoon
 */
class popoon_components_readers_resource extends popoon_components_reader {

    var $attribs = array();
    
	/**
     * Constructor, does nothing at the moment
     */
	function __construct (&$sitemap) {
       parent::__construct($sitemap);
	}

	/**
     * Initiator, called after construction of object
     *
     *  This method will be called in the start element with the attributes from this element
     *
     *  As we just call the parent init method, it's not really needed, 
     *  it's just here for reference
     *
     *  @param $attribs array	associative array with element attributes
     *  @access public
     */
    function init($attribs)
    {
    	parent::init($attribs);
    }   
	
    /**
     * Prints file content 
     *
     * @access public
     */
    function start()
    {
        $mimetype = $this->getAttrib('mime-type');
        $src = str_replace("..","",$this->getAttrib('src'));  
        if ($mimetype == "auto") {
                    $mimetype= $this->getMimeType($src);
        }
        
        if ($mimetype) {
            $this->sitemap->setHeaderAndPrint("Content-Type","$mimetype");
        }
        if (file_exists($src)) {
            $lastModified = filemtime($src);
            $this->sitemap->setHeaderAndPrint("Last-Modified",gmdate('D, d M Y H:i:s T',$lastModified));
            $this->sitemap->setUserData("file-location",$src);
            if (isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) ) {
                if ($lastModified <= strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
                    header( 'HTTP/1.1 304 Not Modified' );
                    header("X-Popoon-Cache-Status: Resource Reader 304");
                    return true;
                } 
            }
        }
        
        if (!@readfile($src)) {
            header("HTTP/1.0 404 Not Found");
            popoon::raiseError($this->getAttrib('src') . " could not be loaded", POPOON_ERROR_WARNING);
        }     
        
    }
    function getMimeType($src) {
        $extension = substr($src,strrpos($src,".")+1);
        
        switch ($extension) {
            case "gif":
                return "image/gif";
            case "jpg":
            case "jpeg":
                return "image/jpeg";
            case "png":
                return "image/png";
            case "css":
                return "text/css";
            case "xml":
            case "xsl":
            case "xsd":
            case "rng":
                return "text/xml";
            case "js":
                return "text/javascript";
            case "html":
            case "htm":
                return "text/html";
            case "txt":
                return "text/plain";
            
            default:
                if (file_exists($src)) {
                    $m =  `file -b $src`;
                    return $m;
                } else {
                    return "text/plain";
                }
        }
        
        
    }
}


?>
