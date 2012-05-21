<?php

include_once("../../../../inc/bx/init.php");
bx_init::start('./conf/config.xml', '../../../../');


$relCssFilename = $_GET['input'];
$absCssFilename = BX_PROJECT_DIR.$relCssFilename;
$cacheFilename = BX_TEMP_DIR.'lessphp_'.str_replace(DIRECTORY_SEPARATOR, '_', $relCssFilename);

if (
    is_readable($cacheFilename)
    && is_readable($absCssFilename)
    && filemtime($absCssFilename) < filemtime($cacheFilename)
) {
    $output = file_get_contents($cacheFilename);
} else {
    include('lessc.inc.php');
    $less = new lessc($absCssFilename);
    try {
        $output = $less->parse();
        file_put_contents($cacheFilename, $output);
    } catch (exception $e) {
        echo $e->getMessage();
    }
}

header('Content-type: text/css');
echo $output;
