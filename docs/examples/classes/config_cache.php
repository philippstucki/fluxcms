<?php

// get an instance
$b = new bx_config::getInstance();
// sets the callback
$b->setOutputCacheCallback("bx_cache");

//the following is optional:

//sets the expire time to 1 hour ( = 3600 sec)
$b->outputCacheExpire = 3600;

$b->cacheContainer = 'file';
$b->cacheParams =   array('cache_dir' => BX_TEMP_DIR . '/cache',
                            'encoding_mode'=>'slash');

function bx_cache() {
	//here comes something, which should return true or false
}
