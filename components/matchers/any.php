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
// $Id: any.php,v 1.2 2004/02/23 23:47:33 chregu Exp $

include_once("popoon/components/matcher.php");

/**
* Matches any popoon vars
*
* any selector matches against map style notation and scheme style notation
* config://dns would be resolved to sth 
* {username} as well
* quite handy for testing responses from actions for example
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: any.php,v 1.2 2004/02/23 23:47:33 chregu Exp $
* @package  popoon
*/
class matcher_any extends matcher
{
    function matcher_any (&$sitemap)
    {
		parent::matcher($sitemap);
    }
	function init($attribs) {
		parent::init($attribs);
		$this->var = $this->getAttrib("var");
	}
	
	function match($value)
    {
		return $this->_match($value,$this->var);
	}

}
