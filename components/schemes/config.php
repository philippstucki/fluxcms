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
// $Id: config.php,v 1.13 2004/03/05 14:37:07 chregu Exp $

/**
* Reads Options/Values from a config file. can be accessed via
*  config://foobar in sitemap
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: config.php,v 1.13 2004/03/05 14:37:07 chregu Exp $
* @package  popoon
* @module   schemes_config
*/

function scheme_config($value)
{
    $config = bx_global::getConfigInstance();
    return $config->$value;
}
	
