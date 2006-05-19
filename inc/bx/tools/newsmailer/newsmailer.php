<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id: fluxcli.php 6796 2006-04-25 13:15:55Z philipp $

ini_set('html_errors', 0);
define('ID', '$Id$');

$commands = array(
    'sendmails',
    'deletemails',
    'checkbounces',
);

$options = array(
    'verbose' => FALSE,
);


if(!file_exists('inc/bx/init.php')) {
    echo "ERROR: please change to the project root and call me again.\n";
    exit(1);
}

include_once("inc/bx/init.php");
bx_init::start('conf/config.xml', '');
$db = $GLOBALS['POOL']->db;


function printHelp() {
    echo "Flux CMS Newsmailer Command Line Interface, ".ID."\n";
    echo "Usage: newsmailer.php [options] <command> [parameters]

send all mails in queue:
    newsmailer.php sendmails

delete all mails in queue:
    newsmailer.php deletemails

check the inbox for bounces and process them:
	newsmailer.php checkbounces <mailbox> <username> <password>
	see http://ch2.php.net/manual/en/function.imap-open.php for <mailbox> syntax
";
    exit(1);
}

function printVerbose($msg) {
    if(VERBOSE)
        echo $msg;
}

function checkArgumentCount($arguments, $count) {
    if(sizeof($arguments) < $count) {
        echo "ERROR: too few arguments\n";
        printHelp();
    }
    return TRUE;
}

function _command_checkbounces($options, $arguments) {

	checkArgumentCount($arguments, 3);    

	 // e.g. {mail.bitflux.ch:143}bouncer", "milo", "xxx";
     $mailbox = imap_open($arguments[0], $arguments[1], $arguments[2]);
     
     print("There are ".imap_num_msg($mailbox)." message in ".$arguments[0]."\n");
     
     for($i =1; $i<=imap_num_msg($mailbox); $i++) {
     	
     	$headers = imap_fetchheader($mailbox, $i);
     	$headers = str_replace(array("\r\n", "\r"), "\n", $headers);
     	
     	$lines = explode("\n", $headers);
     	foreach($lines as $line) {
     		$param = array();
     		eregi("^([^:]*): (.*)", $line, $param);	

			// get the original receiver of this mail
     		if($param[1] == "Return-Path") {
     			$email = str_replace(array('<','>'), '', $param[2]);
     			$parts = array();
     			eregi("^([^\+]*)\+([^@]*)@(.*)", $email, $parts);
     			$email = str_replace('=', '@', $parts[2]);
     			
     			if(!empty($email)) {
					$prefix = $GLOBALS['POOL']->config->getTablePrefix();
			    	$query = "UPDATE ".$prefix."newsletter_users SET bounced=bounced+1 WHERE email='".$email."'";
			    	$bounced = $GLOBALS['POOL']->dbwrite->exec($query);
			    	if($bounced != 1) {
			    		print("No subscription found for: " . $email."\n");
			    	}
			    	else {
			    		print("Bounce: " . $email."\n");	
			    	}
			    	
			    	// if we received more than 4 bounces deactivate the subscription
			    	$query = "UPDATE ".$prefix."newsletter_users SET status=4 WHERE bounced > 4";
			    	$GLOBALS['POOL']->dbwrite->exec($query);			
     			}
     		}
     		
     		// TODO: delete somehow doesn't really work with IMAP
     		imap_delete($mailbox, $i);	
     	}
     }
     
     imap_close($mailbox); 
     
     return TRUE;	
	
}

function _command_sendmails($options, $arguments) {

	$newsmailer = bx_editors_newsmailer_newsmailer::newsMailerFactory("newsmailer");
	$mailserver = $newsmailer->getDefaultMailServer();
    $options = $newsmailer->getMailserverOptions($mailserver);
    		
    echo "starting to send mails\n";
    		
	if($newsmailer->finalizeSend($options, 200)) {
		echo "your mails have been sent";
	}
	else {
		echo "an unexpected error occured";
	}

    return TRUE;
}

function _command_deletemails($options, $arguments) {

    $prefix = $GLOBALS['POOL']->config->getTablePrefix();
    $query = "TRUNCATE TABLE ".$prefix."feeds";
    $GLOBALS['POOL']->dbwrite->query($query);	

	echo "your mails have been deleted";

    return TRUE;
}


$argv = $_SERVER['argv'];
array_shift($argv);

$arguments = array();
$parameters = array();

foreach($argv as $arg) {
    if($arg{0} == '-' && empty($arguments)) {
        switch($arg) {
            case '-v':
            case '--verbose':
                $options['verbose'] = TRUE;
            break;
            case '-h':
            case '--help':
                printHelp();
            default:
                echo "ERROR: unknown option: '$arg'\n";
                printHelp();
        }
    } else {
        $arguments[] = $arg;
    }
}

if(empty($arguments)) {
    printHelp();
}

define('VERBOSE', $options['verbose']);

if(in_array($arguments[0], $commands)) {
    if(call_user_func('_command_'.$arguments[0], $options, array_slice($arguments, 1))) {
        exit(0);
    }
} else {
    echo "ERROR: unknown command: '".$arguments[0]."'\n";
    printHelp();
}

exit(1);

?>