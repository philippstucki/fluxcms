<?php
if (!defined("BX_POPOON_DIR")) {
    define("BX_POPOON_DIR",dirname(__FILE__));
}
function __autoload($class) {
    
    switch (substr($class,0,3)) {
        case "pop":
            if ($class == 'popoon') {
                $incFile = BX_POPOON_DIR.'/popoon.php';
            } else {
                $incFile = BX_POPOON_DIR.str_replace("_","/",substr($class,6)).'.php';
            }
            break;
        default:
            $incFile = str_replace("_","/",$class).".php";
    }
    if (! include_once($incFile)) {
        print("couldn't load $class from $incFile\n");
    }
}
    
 ?>