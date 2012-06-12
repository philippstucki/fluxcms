<?php

include_once("../../init.php");
bx_init::start('./conf/config.xml', '../../../../');

define('MINIFY', TRUE );
define('BX_JS_DIR', BX_THEMES_DIR.$GLOBALS['POOL']->config->theme.'/js/');
define('FILE_SEPARATOR', "\n\n");

if(MINIFY) {
    include('jsmin.php');
}

$jsDir = substr($_SERVER['REDIRECT_URL'], strlen('jsmin/')+1, -3);
$js = '';

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BX_JS_DIR.$jsDir)) as $key => $item) {
    if(substr($item->getFileName(), -3) === '.js') {

        if(MINIFY && substr($item->getFileName(), -7, -3) !== '.min') {
            $relPathname = substr($item->getPathname(), strlen(BX_JS_DIR));
            $cacheFilename = BX_TEMP_DIR."jsmin_".str_replace(DIRECTORY_SEPARATOR, '_', $relPathname);

            if(is_readable($cacheFilename) && $item->getMTime() < filemtime($cacheFilename)) {
                $js .= file_get_contents($cacheFilename);
            } else {
                $jsMin = JSMin::minify(file_get_contents($item->getPathname())).FILE_SEPARATOR;
                $js .= $jsMin;
                file_put_contents($cacheFilename, $jsMin);
            }

        } else {
            if(is_readable($item->getPathname())) {
                $js .= file_get_contents($item->getPathname()).FILE_SEPARATOR;
            }
        }
    }
}

header('Content-type: application/x-javascript');
echo $js;
