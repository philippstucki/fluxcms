<?php

include_once("../../../../inc/bx/init.php");
bx_init::start('./conf/config.xml', '../../../../');

$conf = bx_config::getInstance();

$relCssFilename = $_GET['input'];
$absCssFilename = BX_PROJECT_DIR.$relCssFilename;
$cacheFilename = BX_TEMP_DIR.'lessphp_'.str_replace(DIRECTORY_SEPARATOR, '_', $relCssFilename);

if (
    is_readable($cacheFilename)
    && is_readable($absCssFilename)
    && filemtime($absCssFilename) < filemtime($cacheFilename)
    && $conf->environment !== 'dev'
) {
    $output = file_get_contents($cacheFilename);
} else {
    include('lessc.inc.php');
    $less = new lessc($absCssFilename);

    $less->registerFunction("fontsize-set", function($arg, $ctx) {
        list($type, $value) = $arg;
        list($fst, $fsv) = $ctx->get('@font-size');
        $ctx->set('@font-size', array('px', $value));
        $ctx->set('@em', array('em', 1/$value));
        return array('em', $value/$fsv);
    });

    $less->registerFunction("em", function($arg, $ctx) {
        list($type, $value) = $arg;
        list($pxt, $pxv) = $ctx->get('@em');
        return array('em', $value*$pxv);
    });

    if ($conf->environment === 'dev') {
        $less->setFormatter('indent');
    } else {
        $less->setFormatter('compressed');
    }

    try {
        $output = $less->parse(
            null,
            array(
                'fluxcms-theme-root' => "'".BX_WEBROOT_THEMES.bx_helpers_config::getOption('theme')."/'",
                'font-size' => '16px',
                'em' => '1/16em',
            )
        );
        file_put_contents($cacheFilename, $output);
    } catch (exception $e) {
        echo $e->getMessage();
    }
}

header('Content-type: text/css');
header('Cache-Control: public');
echo $output;
