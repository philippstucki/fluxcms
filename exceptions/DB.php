<?php
class PopoonDBException extends Exception {
    
    function __construct($err) {
        $this->message = $err->getMessage();
        $this->userInfo =   $err->getUserInfo();
        parent::__construct();
    }
}
?>
