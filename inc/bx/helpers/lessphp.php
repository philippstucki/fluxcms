<?php

class bx_helpers_lessphp {

    static function getFileVersion($lessFile) {
        return substr(sha1(abs(filemtime(BX_PROJECT_DIR.$lessFile) - 1255039200)), 0, 8);
    }
}
