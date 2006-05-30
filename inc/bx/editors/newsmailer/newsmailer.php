<?php

function start_time_test() {
      global $start_time_test;
      $start_time_test = time() + microtime(true);
}

function stop_time_test() {
      global $start_time_test;
      $stop_time_test = time() + microtime(true);
      $time = $stop_time_test - $start_time_test;
      bx_helpers_debug::webdump($time);
}

/**
 * Factory and default class to send newsletters (using Mail_Queue)
 */
class bx_editors_newsmailer_newsmailer {    
    
    protected static $htmlImages = array();
    protected $db_options;
    
    /**
     * Configure Mail_Queue database options
     */
    public function __construct()
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
		$this->db_options['type']       = 'db';
		$this->db_options['dsn']        = $GLOBALS['POOL']->dbwrite->getDSN();
		$this->db_options['mail_table'] = $prefix.'mail_queue';	
    }
    
    /**
     * Factory to create an instance of a newsmailer from its name
     */
    final public static function newsMailerFactory($name)
    {
    	$module = "bx_editors_newsmailer_".$name;
    	if(class_exists($module)) {
			return new $module();
        }
		return null;
    }
    
	/**
	 * Creates the mail_options structure for sending mails over Mail_Queue
	 */
	final public static function getMailserverOptions($id)
	{
        $prefix = $GLOBALS['POOL']->config->getTablePrefix();
        $query = "select * from ".$prefix."newsletter_mailservers where id=".$id;
        $server = $GLOBALS['POOL']->db->queryRow($query, null, MDB2_FETCHMODE_ASSOC);	
        
		$mail_options = array();
		$mail_options['driver']    = 'smtp';
		$mail_options['host']      = $server['host'];
		$mail_options['port']      = $server['port'];
		$mail_options['auth']      = empty($server['username']) ? false : true;
		$mail_options['username']  = $server['username'];
		$mail_options['password']  = $server['password'];	
		//$mail_options['localhost'] = 'localhost'; //optional Mail_smtp parameter
		
		return $mail_options;
	}
    
    /**
     * Creates all the MIME mail bodies for the selected newsletter and saves them into the mail queue
     */
    public function autoPrepareNewsletter($draftId)
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
		$query = "SELECT DISTINCT u.* FROM ".$prefix."newsletter_cache c, ".$prefix."newsletter_users u WHERE c.fk_draft=".$draftId." AND c.fk_user=u.id";
    	$receivers = $GLOBALS['POOL']->db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
    	
    	$draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);
    	$mailoptions = $this->getMailserverOptions($draft['mailserver']);
    	
    	// read in the newsletter templates if existing
    	$htmlMessage = $this->readNewsletterFile($draft['htmlfile'], "html");
		$textMessage = $this->readNewsletterFile($draft['textfile'], "text");

		$dom = new DomDocument();
		$dom->loadXML($htmlMessage);
		
		$dom = $this->transformHTML($dom);
		
		if($draft["embed"] == 1) {
			self::$htmlImages = array();
			$dom = $this->transformHTMLImages($dom);
    	}

		$htmlTransform = $dom->saveXML();
	
		$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$hdrs = array( 'From'    => $draft['from']);

		$mime =& new Mail_mime();

		if($draft["embed"] == 1) {
			// Add images to MIME Body
			foreach(self::$htmlImages as $image) {
				$type = end(explode(".", $image["name"]));
				$mime->addHTMLImage($image["content"], "image/".$type, $image["name"], false);
			}
		}

		// Iterate over all newsletter receivers
		foreach($receivers as $person)
		{
			// create the personalized email 
			$customHtml = $this->customizeMessage($htmlTransform, $person, $draft['htmlfile']);
			$customText = $this->customizeMessage($textMessage, $person, $draft['textfile']);
			
			// Generate the MIME body, it's possible to attach both a HTML and a Text version for the newsletter
			if($textMessage !== false)
				$mime->setTXTBody(utf8_decode($customText));
			if($htmlMessage !== false)
				$mime->setHTMLBody(utf8_decode($customHtml));

			$body = $mime->get();
			$hdrs = $mime->headers($hdrs);			
			$hdrs['Subject'] = $draft['subject'];
			$hdrs['To'] = $person['email'];
			$hdrs['Return-Path'] = $this->getBounceAddress($person);

			// Put it in the queue (the message will be cached in the database)
			$mail_queue->put($hdrs['From'], $person['email'], $hdrs, $body );
			
	    	$query = "UPDATE ".$prefix."newsletter_cache SET status='2' WHERE fk_user='".$person['id']."' AND fk_draft='".$draftId."'";
	    	$GLOBALS['POOL']->dbwrite->exec($query);
		}

		// all mails for this draft were preprocessed successfully
    	$query = "UPDATE ".$prefix."newsletter_drafts SET prepared=NOW() WHERE id=".$draftId;
    	$GLOBALS['POOL']->dbwrite->exec($query);
    }
    
    /**
     * Send all the mails in the queue at once
     */
    public function autoSendNewsletter($draftId)
    {
    	$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$draft = $GLOBALS['POOL']->db->queryRow("select * from ".$prefix."newsletter_drafts WHERE ID=".$draftId, null, MDB2_FETCHMODE_ASSOC);
    	$mailoptions = $this->getMailserverOptions($draft['mailserver']);
    	
    	$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$retval = $mail_queue->sendMailsInQueue();	
		
    	$query = "UPDATE ".$prefix."newsletter_drafts SET sent=NOW() WHERE id=".$draftId;
    	$GLOBALS['POOL']->dbwrite->exec($query);
		
		return !$mail_queue->isError($retval);    	
    }
    
    /**
     * Sends a bunch of mails
     */
    public function sendNewsletter($draft, $receivers, $mailoptions, $embedImages = false)
    {
 		$start_time_test = time() + microtime(true);
 		
    	// read in the newsletter templates if existing
    	$htmlMessage = $this->readNewsletterFile($draft['htmlfile'], "html");
		$textMessage = $this->readNewsletterFile($draft['textfile'], "text");

		$dom = new DomDocument();
		$dom->loadXML($htmlMessage);
		
		$dom = $this->transformHTML($dom);
		
		if($embedImages) {
			self::$htmlImages = array();
			$dom = $this->transformHTMLImages($dom);
    	}

		$htmlTransform = $dom->saveXML();
	
		$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$hdrs = array( 'From'    => $draft['from']);

		$mime =& new Mail_mime();

		if($embedImages) {
			// Add images to MIME Body
			foreach(self::$htmlImages as $image) {
				$type = end(explode(".", $image["name"]));
				$mime->addHTMLImage($image["content"], "image/".$type, $image["name"], false);
			}
		}
		
		$stop_time_test = time() + microtime(true);
      	$time = $stop_time_test - $start_time_test;
      	bx_helpers_debug::webdump("Preparation " . $time);

		// Iterate over all newsletter receivers
		foreach($receivers as $person)
		{
			$start_time_test = time() + microtime(true);
			
			// create the personalized email 
			$customHtml = $this->customizeMessage($htmlTransform, $person, $draft['htmlfile']);
			$customText = $this->customizeMessage($textMessage, $person, $draft['textfile']);
			
			$stop_time_test = time() + microtime(true);
      		$time = $stop_time_test - $start_time_test;
      		bx_helpers_debug::webdump("Custom " . $time);
      		
			// Generate the MIME body, it's possible to attach both a HTML and a Text version for the newsletter
			if($textMessage !== false)
				$mime->setTXTBody(utf8_decode($customText));
			if($htmlMessage !== false)
				$mime->setHTMLBody(utf8_decode($customHtml));

			$stop_time_test = time() + microtime(true);
      		$time = $stop_time_test - $start_time_test;
      		bx_helpers_debug::webdump("Body " . $time);

			// TODO: this method sucks
			$body = $mime->get();
			
			$stop_time_test = time() + microtime(true);
      		$time = $stop_time_test - $start_time_test;
      		bx_helpers_debug::webdump("mime get " . $time);
      		
			$hdrs = $mime->headers($hdrs);			
			
						$stop_time_test = time() + microtime(true);
      		$time = $stop_time_test - $start_time_test;
      		bx_helpers_debug::webdump("create headers " . $time);
      							
			$hdrs['Subject'] = $draft['subject'];
			$hdrs['To'] = $person['email'];
			$hdrs['Return-Path'] = $this->getBounceAddress($person);

			$stop_time_test = time() + microtime(true);
      		$time = $stop_time_test - $start_time_test;
      		bx_helpers_debug::webdump("hdrs " . $time);
			
			// Put it in the queue (the message will be cached in the database)
			$mail_queue->put($hdrs['From'], $person['email'], $hdrs, $body );
			
			$stop_time_test = time() + microtime(true);
      		$time = $stop_time_test - $start_time_test;
      		bx_helpers_debug::webdump("Put " . $time);
		}

		// wait a second before we send, otherwise the queue seems to be empty (bug)
    	sleep(1);
		
		$this->finalizeSend($mailoptions);
    }
    
    /**
     * If double-opt-in is set, send the user an email in order to confirm his subscription
     */
    public function sendActivationMail($person, $mailserver, $mailfrom, $mailsub, $mailtext, $mailhtml)
    {
    	if($mailserver === null) {
			$mailserver = "default";
    	}

		$prefix = $GLOBALS['POOL']->config->getTablePrefix();
    	$query = "select id from ".$prefix."newsletter_mailservers where descr='".$mailserver."'";
    	$mailserver = $GLOBALS['POOL']->db->queryOne($query);			

		$options = $this->getMailserverOptions($mailserver);
		
		$draft = array(	"from" => $mailfrom,
						"subject" => $mailsub,
						"textfile" => $mailtext,
						"htmlfile" => $mailhtml);

		$this->sendNewsletter($draft, array($person), $options);
    }
    
    /**
     * Is called to indicate all messages are saved in the queue
     */
    public function finalizeSend($mailoptions, $maxAmount = 1000)
    {
    	$mail_queue = new Mail_Queue($this->db_options, $mailoptions);
		$retval = $mail_queue->sendMailsInQueue($maxAmount);	
		return !$mail_queue->isError($retval);
    }
    
    /**
     * Add custom style to the HTML document
     */
    protected function transformHTML($inputdom)
    {
		return $inputdom;   	  	
    }
    
    /**
     * Embedds images directly into the HTML document
     */
    protected function transformHTMLimages($inputdom)
    {
		$xsl = new DomDocument();
		$xsl->load('themes/3-cols/htmlimage.xsl');
		$proc = new XsltProcessor();
		$proc->registerPHPFunctions();
		$xsl = $proc->importStylesheet($xsl);
		return $proc->transformToDoc($inputdom);  	
    }
    
    /**
     * Customized the message for a certain user
     * tags in the form of {field} are replaced with its corresponding value from the database
     */
    protected function customizeMessage($message, $person, $filename)
    {
    	$templates = array();
    	$values = array();
    	foreach($person as $key=>$val) {
    		array_push($templates, '{' . $key . '}');
    		array_push($values, $val);
    	}
    	
    	$webfilename = str_replace(array('en.xhtml', 'de.xhtml'), 'html', $filename);

    	array_push($templates, '{title}', '{weblink}', '{activate}', '{unsubscribe}', '{publication}', '{date}');
    	
    	array_push($values, $person['gender'] == '0' ? 'Herr' : 'Frau');
    	array_push($values, BX_WEBROOT);
    	array_push($values, BX_WEBROOT."newsletter/index.html?activate=".$person['activation']);
    	array_push($values, BX_WEBROOT."newsletter/index.html?unsubscribe=".$person['email']);
    	array_push($values, BX_WEBROOT."newsletter/archive/".$webfilename);
    	array_push($values, date("m/Y"));
    	
    	return str_replace($templates, $values, $message);
    }

    /**
     * Reads a newsletter resource file and returns its content
     */
    protected function readNewsletterFile($name, $type)
    {
    	return @file_get_contents('data/newsletter/archive/'.$name);
    }
    
    /**
     * Creates the bounce (return-path) address for the message
     */
    protected function getBounceAddress($parameters)
    {
		// Generate a special bounce address e.g. fluxcms-bounces+milo=bitflux.ch@bitflux.ch
		$bounceEmail = str_replace("@", "=", $parameters['email']);
		return "milo+".$bounceEmail."@bitflux.ch";    	
    }
    
    /**
     * Callback function from htmlimage.xsl
     * Preloads the images found and returns a short filename in order to reference them as embedded HTML images
     */
    public static function adjustImagePath($path)
    {
    	// extract filename
    	$path = ltrim($path, "/");
    	$shortname = end(preg_split("[\\/]", $path));	
    	
    	if(($content = file_get_contents($path)) != false) {
    		
    		// save the loaded image, this array is being read by sendNewsletter() later
    		self::$htmlImages[] = array("name" => $shortname, "content" => $content);
    	}
    	
    	return $shortname;
    }
}

?>
