<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
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


/**
* Matches any popoon vars
*
* any selector matches against map style notation and scheme style notation
* config://dns would be resolved to sth 
* {username} as well
* quite handy for testing responses from actions for example
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class popoon_components_selectors_any extends popoon_components_selector
{

    function __construct(&$sitemap)
    {
		parent::__construct($sitemap);
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
