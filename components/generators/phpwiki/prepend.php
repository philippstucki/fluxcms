<?php

/* lib/prepend.php
 *
 * Things which must be done and defined before anything else.
 */
$RCS_IDS = '';
function rcs_id ($id) { 
}
rcs_id('$Id: prepend.php,v 1.1 2003/04/16 20:30:26 chregu Exp $');

// Used for debugging purposes
class DebugTimer {
    function DebugTimer() {
        $this->_start = $this->microtime();
        if (function_exists('posix_times'))
            $this->_times = posix_times();
    }

    /**
     * @param string $which  One of 'real', 'utime', 'stime', 'cutime', 'sutime'
     * @return float Seconds.
     */
    function getTime($which='real', $now=false) {
        if ($which == 'real')
            return $this->microtime() - $this->_start;

        if (isset($this->_times)) {
            if (!$now) $now = posix_times();
            $ticks = $now[$which] - $this->_times[$which];
            return $ticks / $this->_CLK_TCK();
        }

        return 0.0;           // Not available.
    }

    function getStats() {
        if (!isset($this->_times)) {
            // posix_times() not available.
            return sprintf("real: %.3f", $this->getTime('real'));
        }
        $now = posix_times();
        return sprintf("real: %.3f, user: %.3f, sys: %.3f",
                       $this->getTime('real'),
                       $this->getTime('utime', $now),
                       $this->getTime('stime', $now));
    }
        
    function _CLK_TCK() {
        // FIXME: this is clearly not always right.
        // But how to figure out the right value?
        return 100.0;
    }

    function microtime(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}
$RUNTIMER = new DebugTimer;

error_reporting(E_ALL);
require_once('lib/ErrorManager.php');
require_once('lib/WikiCallback.php');

// FIXME: deprecated
function ExitWiki($errormsg = false)
{
    global $request;
    static $in_exit = 0;

    if (is_object($request))
        $request->finish($errormsg); // NORETURN

    if ($in_exit)
        exit;
    
    $in_exit = true;

    global $ErrorManager;
    $ErrorManager->flushPostponedErrors();
   
    if(!empty($errormsg)) {
        PrintXML(HTML::br(), $errormsg);
        print "\n</body></html>";
    }
    exit;
}

  $GLOBALS['ErrorManager']->setPostponedErrorMask(E_ALL);


// (c-file-style: "gnu")
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:   
?>
