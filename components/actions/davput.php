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

include_once("popoon/components/action.php");
/**
* Class for generating xml document
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/

class action_davput extends action {

    /**
    * Constructor
    *
    */
    function action_davput(&$sitemap) {
        $this->action($sitemap);
    }

    function init() {
    }
    
    function act() {
        
        
        
        // read data from php://input stream
        $xml = "";
        $fd = fopen("php://input","r");
        while ($line = fread($fd,2048)) {
            $xml .= $line;
        }
        fclose($fd);
        $src = $this->getParameterDefault("src");
        
        $fd=fopen($src,"w");
        
        fwrite($fd,$xml);
        fclose($fd);
        // TODO: Error handling!
        $this->sitemap->setResponseCode(204);
        return array("message" => "Data saved");
        
    }

}

?>
