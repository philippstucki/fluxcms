<?php
class PopoonXSLTParseErrorException extends Exception {
    
    public function __construct($filename) {
        $dom = new DomDocument();
        $xsl = new DomDocument();
        $dom->load($filename);
        $xsl->load($filename);
        set_error_handler(array($this,"errorHandler"));
        $proc = new XSLTProcessor();
        $proc->importStyleSheet($xsl);
        $proc->transformToDoc($dom);
        restore_error_handler();
        
        //FIXME: Give more info, what went wrong
        $this->message = "XSLT Error in $filename";
        parent::__construct();
        
    }
    
    public function errorHandler($errno, $errstr, $errfile, $errline) 
    {
        $pos = strpos($errstr,"]:") ;
        if ($pos) {
            $errstr = substr($errstr,$pos+ 2);
        }
        $this->userInfo .="$errstr<br />\n";
    }
    
}
?>
