<?php
class PopoonXMLParseErrorException extends Exception {
    
    function __construct($filename) {
        //FIXME: Give more info, what went wrong
        $this->message = "XML Parse Error in $filename";
        parent::__construct();
    }
}
?>
