<?php
class PopoonDBException extends Exception {
    
    function __construct($err) {
        $this->message =htmlspecialchars( $err->getMessage());
        //don't leak username:password to the outside
        
        $this->userInfo =  htmlspecialchars( preg_replace("#//([^:]*):([^\@^:]*)\@#","//*******:********@",$err->getUserInfo()));
        $this->code = $err->getCode();
        parent::__construct();
    }
}
?>
