<?php

/**
 * MITLinks Newsletter
 */
class bx_editors_newsmailer_mitlinks extends bx_editors_newsmailer_newsmailer {    
   
   protected static $textHeader;
   
    /**
     * Sends a bunch of mails
     */
    public function sendNewsletter($draft, $receivers, $mailoptions, $embedImages = false)
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();

    	// mysqli(mysqli)://fluxcms:fluxcms@localhost/fluxcms
    	eregi("^([^//]*)//([^:]*):([^@]*)@([^/]*)/(.*)", $GLOBALS['POOL']->dbwrite->getDSN(), $dbparams);
    	
    	self::$textHeader = 
'dbhost: '.$dbparams[4].'
dbuser: '.$dbparams[2].'
dbpass: '.$dbparams[3].'
dbtable: '.$prefix.'mail_queue
dbname:  '.$dbparams[5].'
listname: testliste
password: daspasswort!

STARTMESSAGE
To: <TMPL_VAR NAME="firstname"> <TMPL_VAR NAME="lastname"> <<TMPL_VAR
NAME="email">>
From: '.$draft['from'].'
Subject: '.$draft['subject'].'

';

    	parent::sendNewsletter($draft, $receivers, $mailoptions, $embedImages);
    }
   
    /**
     * Reads a newsletter resource file and returns its content
     */
    protected function readNewsletterFile($name, $type)
    {
    	$content = parent::readNewsletterFile($name, $type);
    	
    	if($type == "text") {
	    	// Add the MITLinks specific header for their spambot
	    	$content = self::$textHeader.$content;
    	}
    	
		return $content;
    }
   
    /**
     * Add custom style to the HTML document to replace the missing .css style sheet
     */
    protected function transformHTML($inputdom)
    {
		$xsl = new DomDocument();
		$xsl->load('themes/3-cols/scansystems.xsl');
		$proc = new XsltProcessor();
		$xsl = $proc->importStylesheet($xsl);
		return $proc->transformToDoc($inputdom);  	
    }
}

?>