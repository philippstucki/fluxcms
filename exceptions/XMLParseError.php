<?php
class PopoonXMLParseErrorException extends Exception {
    
    public function __construct($filename) {
        set_error_handler(array($this,"errorHandler"));
        $dom = new DomDocument();
        $dom->load($filename);
        restore_error_handler();
        //FIXME: Give more info, what went wrong
        $this->message = "XML Parse Error in $filename";
        parent::__construct();
    }
    
    public function errorHandler($errno, $errstr, $errfile, $errline) {
        $pos = strpos($errstr,"]:") ;
        if ($pos) {
            $errstr = substr($errstr,$pos+ 2);
        }
        $this->userInfo .="$errstr<br />\n";
    }
}
?>
