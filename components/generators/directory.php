<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// | Copyright (c) 2003 Mike Hommey                                       |
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
// | Author: Mike Hommey <mh@glandium.org>                                |
// |         Christian Stocker <chregu@bitflux.ch                         |
// +----------------------------------------------------------------------+
//
// $Id: directory.php,v 1.5 2004/02/24 10:38:14 chregu Exp $

include_once('popoon/components/generator.php');

/**
* This class returns xml from directory listing
*
* @author   Mike Hommey <mh@glandium.org>
* @version  $Id: directory.php,v 1.5 2004/02/24 10:38:14 chregu Exp $
* @package  popoon
*/
class generator_directory extends generator {
 var $mimeType = false;

    /**
    * Constructor, does nothing at the moment
    */
    function generator_directory (&$sitemap) {
        $this->generator($sitemap);
       
    }

    /**
    * generates an xml-DomDocument out of the xml-file
    *
    * @access public
    * @returns object DomDocument XML-Document
    */
    function DomStart(&$xml)
    {
        if ($this->mimeType && !function_exists("mime_content_type")) {
            $this->mimeType = false;
        }
	$src = $this->getAttrib('src');
	$xml = domxml_new_doc('1.0');
	$root = $xml->create_element_ns('http://apache.org/cocoon/directory/2.0','directory','dir');
	$xml->append_child($root);
    if (!$depth = $this->getParameter('depth')) {
        $depth = 1;
    }
    
	if ($this->read_directory($xml,$root,$this->sitemap->base_dir.'/'.$src, $depth)) {
		$stat = stat($this->sitemap->base_dir.'/'.$src);
		$root->set_attribute('size',$stat['size']);
		$root->set_attribute('lastModified',$stat['mtime']*1000);
        $root->set_attribute('lastModifiedSecs',$stat['mtime']);
	} else {
		$xml = NULL;
	}
        return True;
    }

    function read_directory($dom, $parent, $directory, $depth = 1) {
	if (is_dir($directory) && ($dh = opendir($directory))) {
		while (($file = readdir($dh)) !== false) {
			if (($file != ".") && ($file != "..")) {
				if (is_file($directory.'/'.$file)) {
					$node = $dom->create_element('file');
				} else if (is_dir($directory.'/'.$file)) {
					$node = $dom->create_element('directory');
				}
				$node->set_attribute('name', $file);
				$stat = stat($directory.'/'.$file);
                if ($this->mimeType) {
                    $mimeType = mime_content_type($directory.'/'.$file);
                    if (strpos($mimeType, "image") === 0) {
                        $size = getimagesize(  $directory.'/'.$file);
                        $node->set_attribute('imageHeight', $size[1]);
                        $node->set_attribute('imageWidth', $size[0]);
                    }
                    $node->set_attribute('mimeType', $mimeType);
                }
				$node->set_attribute('size',$stat['size']);
                $node->set_attribute('lastModified',$stat['mtime']*1000);
				$node->set_attribute('lastModifiedSecs', $stat['mtime']);
				$parent->append_child($node);
				$node->set_namespace('http://apache.org/cocoon/directory/2.0');
				if (is_dir($directory.'/'.$file) and ($depth > 1)) {
					$this->read_directory($dom, $node, $directory.'/'.$file, $depth - 1);
				}
			}
		}
		return true;
	} else {
		return false;
	}
    }
    
    /* CACHING STUFF */

    /**
     * Generate cacheKey
     *
     * Calls the method inherited from 'Component'
     *
     * @param   array  attributes
     * @param   int    last cacheKey
     * @see     generateKeyDefault()
     */
    function generateKey($attribs, $keyBefore){
        return($this->generateKeyDefault($attribs, $keyBefore));
    }

    /** Generate validityObject  
     *
     * This is common to all "readers", you'll find the same code there.
     * I'm thinking about making a method in the class component named generateValidityFile() or alike
     * instead of having the same code everywhere..
     *
     * @author Hannes Gassert <hannes.gassert@unifr.ch>
     * @see  checkvalidity()
     * @return  array  $validityObject contains the components attributes plus file modification time and time of last access.
     */
    function generateValidity(){
        $validityObject = $this->attribs;
        $src = $this->getAttrib('src');
        $validityObject['filemtime'] = filemtime($src);
        $validityObject['fileatime'] = fileatime($src);
        return($validityObject);
    }

    /**
     * Check validity of a validityObject from cache
     *
     * This implements only the most simple form: If there's no fresher version, take that from cache.
     * I guess we'll need some more refined criteria..
     *
     * @return  bool  true if the validityObject indicates that the cached version can be used, false otherwise.
     * @param   object  validityObject
     */
    function checkValidity($validityObject){
        return(isset($validityObject['src'])       &&
               isset($validityObject['filemtime']) &&
               file_exists($validityObject['src']) &&
               ($validityObject['filemtime'] == filemtime($validityObject['src'])));
    }

}


?>
