<?php

rcs_id('$Id: WikiRequest.php,v 1.1 2003/04/16 20:30:26 chregu Exp $');


include_once "lib/config.php";
require_once("lib/stdlib.php");
require_once('lib/Request.php');
require_once("lib/WikiUser.php");
require_once('lib/WikiDB.php');

$GLOBALS['UserPreferences'] = $UserPreferences;




class WikiRequest extends Request {
    // var $_dbi;

    function WikiRequest () {
        $this->_dbi = WikiDB::open($GLOBALS['DBParams']);

        if (USE_DB_SESSION) {
            include_once('lib/DB_Session.php');
            $this->_dbsession = & new DB_Session($this->getDbh(),
                                                 $GLOBALS['DBParams']['db_session_table']);
        }
        
        $this->Request();

        // Normalize args...
        $this->setArg('pagename', $this->_deducePagename());
        $this->setArg('action', $this->_deduceAction());

        // Restore auth state
        $this->_user = new WikiUser($this, $this->_deduceUsername());
        // $this->_user = new WikiDB_User($this->_user->getId(), $this->getAuthDbh());
        $this->_prefs = $this->_user->getPreferences();

    }

    function initializeLang () {
        if ($user_lang = $this->getPref('lang')) {
            //trigger_error("DEBUG: initializeLang() ". $user_lang ." calling update_locale()...");
            update_locale($user_lang);
        }
    }

    function initializeTheme () {
    	global $Theme;

        // Load theme
        if ($user_theme = $this->getPref('theme'))
            include_once("themes/$user_theme/themeinfo.php");
        if (empty($Theme) and defined ('THEME'))
            include_once("themes/" . THEME . "/themeinfo.php");
        if (empty($Theme))
            include_once("themes/default/themeinfo.php");
        assert(!empty($Theme));
    }


    // This really maybe should be part of the constructor, but since it
    // may involve HTML/template output, the global $request really needs
    // to be initialized before we do this stuff.
    function updateAuthAndPrefs () {
        
        // Handle preference updates, an authentication requests, if any.
        if ($new_prefs = $this->getArg('pref')) {
            $this->setArg('pref', false);
            if ($this->isPost() and !empty($new_prefs['passwd']) and 
                ($new_prefs['passwd2'] != $new_prefs['passwd'])) {
                // FIXME: enh?
                $this->_prefs->set('passwd','');
                // $this->_prefs->set('passwd2',''); // This is not stored anyway
                return false;
            }
            foreach ($new_prefs as $key => $val) {
            	if ($key == 'passwd') {
                    // FIXME: enh?
                    $val = crypt('passwd');
            	}
                $this->_prefs->set($key, $val);
            }
        }

        // FIXME: need to move authentication request processing
        // up to be before pref request processing, I think,
        // since logging in may change which preferences
        // we're talking about...

        // Handle authentication request, if any.
        if ($auth_args = $this->getArg('auth')) {
            $this->setArg('auth', false);
            $this->_handleAuthRequest($auth_args); // possible NORETURN
        }
        elseif ( ! $this->_user->isSignedIn() ) {
            // If not auth request, try to sign in as saved user.
            if (($saved_user = $this->getPref('userid')) != false) {
                $this->_signIn($saved_user);
            }
        }

        // Save preferences in session and cookie
        // FIXME: hey! what about anonymous users?   Can't they have
        // preferences too?

        $id_only = true; 
        $this->_user->setPreferences($this->_prefs, $id_only);

        // Ensure user has permissions for action
        $require_level = $this->requiredAuthority($this->getArg('action'));
        if (! $this->_user->hasAuthority($require_level))
            $this->_notAuthorized($require_level); // NORETURN
    }

    function getUser () {
        return $this->_user;
    }

    function getPrefs () {
        return $this->_prefs;
    }

    // Convenience function:
    function getPref ($key) {
        return $this->_prefs->get($key);
    }

    function getDbh () {
        return $this->_dbi;
    }

    function getAuthDbh () {
        global $DBParams, $DBAuthParams;
        if (!isset($this->_auth_dbi)) {
            if ($DBParams['dbtype'] == 'dba' or empty($DBAuthParams['auth_dsn']))
                $this->_auth_dbi = $this->getDbh(); // use phpwiki database 
            elseif ($DBAuthParams['auth_dsn'] == $DBParams['dsn'])
                $this->_auth_dbi = $this->getDbh(); // same phpwiki database 
            else // use external database 
                // needs PHP 4.1. better use $this->_user->...
                $this->_auth_dbi = WikiDB_User::open($DBAuthParams);
        }
        return $this->_auth_dbi;
    }

    /**
     * Get requested page from the page database.
     * By default it will grab the page requested via the URL
     *
     * This is a convenience function.
     * @param string $pagename Name of page to get.
     * @return WikiDB_Page Object with methods to pull data from
     * database for the page requested.
     */
    function getPage ($pagename = false) {
        if (!isset($this->_dbi))
            $this->getDbh();
		if (!$pagename) 
			$pagename = $this->getArg('pagename');
        return $this->_dbi->getPage($pagename);
    }

    /** Get URL for POST actions.
     *
     * Officially, we should just use SCRIPT_NAME (or some such),
     * but that causes problems when we try to issue a redirect, e.g.
     * after saving a page.
     *
     * Some browsers (at least NS4 and Mozilla 0.97 won't accept
     * a redirect from a page to itself.)
     *
     * So, as a HACK, we include pagename and action as query args in
     * the URL.  (These should be ignored when we receive the POST
     * request.)
     */
    function getPostURL ($pagename=false) {
        if ($pagename === false)
            $pagename = $this->getArg('pagename');
        $action = $this->getArg('action');
        return WikiURL($pagename, array('action' => $action));
    }
    
    function _handleAuthRequest ($auth_args) {
        if (!is_array($auth_args))
            return;

        // Ignore password unless POST'ed.
        if (!$this->isPost())
            unset($auth_args['passwd']);

        $user = $this->_user->AuthCheck($auth_args);

        if (isa($user, 'WikiUser')) {
            // Successful login (or logout.)
            $this->_setUser($user);
        }
        elseif ($user) {
            // Login attempt failed.
            $fail_message = $user;
            $auth_args['pass_required'] = true;
            // If no password was submitted, it's not really
            // a failure --- just need to prompt for password...
            if (!isset($auth_args['passwd'])) {
                 //$auth_args['pass_required'] = false;
                 $fail_message = false;
            }
            $this->_user->PrintLoginForm($this, $auth_args, $fail_message);
            $this->finish();    //NORETURN
        }
        else {
            // Login request cancelled.
        }
    }

    /**
     * Attempt to sign in (bogo-login).
     *
     * Fails silently.
     *
     * @param $userid string Userid to attempt to sign in as.
     * @access private
     */
    function _signIn ($userid) {
        $user = $this->_user->AuthCheck(array('userid' => $userid));
        if (isa($user, 'WikiUser')) {
            $this->_setUser($user); // success!
        }
    }

    function _setUser ($user) {
        $this->_user = $user;
        $this->setCookieVar('WIKI_ID', $user->_userid, 365);
        $this->setSessionVar('wiki_user', $user);
        if ($user->isSignedIn())
            $user->_authhow = 'signin';

        // Save userid to prefs..
        $this->_prefs->set('userid',
                           $user->isSignedIn() ? $user->getId() : '');
    }

    function _notAuthorized ($require_level) {
        // User does not have required authority.  Prompt for login.
        $what = $this->getActionDescription($this->getArg('action'));

        if ($require_level >= WIKIAUTH_FORBIDDEN) {
            $this->finish(fmt("%s is disallowed on this wiki.",
                              $this->getDisallowedActionDescription($this->getArg('action'))));
        }
        elseif ($require_level == WIKIAUTH_BOGO)
            $msg = fmt("You must sign in to %s.", $what);
        elseif ($require_level == WIKIAUTH_USER)
            $msg = fmt("You must log in to %s.", $what);
        else
            $msg = fmt("You must be an administrator to %s.", $what);
        $pass_required = ($require_level >= WIKIAUTH_USER);

        $this->_user->PrintLoginForm($this, compact('require_level','pass_required'), $msg);
        $this->finish();    // NORETURN
    }

    function getActionDescription($action) {
        static $actionDescriptions;
        if (! $actionDescriptions) {
            $actionDescriptions
            = array('browse'     => _("browse pages in this wiki"),
                    'diff'       => _("diff pages in this wiki"),
                    'dumphtml'   => _("dump html pages from this wiki"),
                    'dumpserial' => _("dump serial pages from this wiki"),
                    'edit'       => _("edit pages in this wiki"),
                    'create'	 => _("create pages in this wiki"),
                    'loadfile'   => _("load files into this wiki"),
                    'lock'       => _("lock pages in this wiki"),
                    'remove'     => _("remove pages from this wiki"),
                    'unlock'     => _("unlock pages in this wiki"),
                    'upload'     => _("upload a zip dump to this wiki"),
                    'verify'     => _("verify the current action"),
                    'viewsource' => _("view the source of pages in this wiki"),
                    'xmlrpc'	 => _("access this wiki via XML-RPC"),
                    'zip'        => _("download a zip dump from this wiki"),
                    'ziphtml'    => _("download an html zip dump from this wiki")
                    );
        }
        if (in_array($action, array_keys($actionDescriptions)))
            return $actionDescriptions[$action];
        else
            return $action;
    }
    function getDisallowedActionDescription($action) {
        static $disallowedActionDescriptions;
        if (! $disallowedActionDescriptions) {
            $disallowedActionDescriptions
            = array('browse'     => _("Browsing pages"),
                    'diff'       => _("Diffing pages"),
                    'dumphtml'   => _("Dumping html pages"),
                    'dumpserial' => _("Dumping serial pages"),
                    'edit'       => _("Editing pages"),
                    'create'     => _("Creating pages"),
                    'loadfile'   => _("Loading files"),
                    'lock'       => _("Locking pages"),
                    'remove'     => _("Removing pages"),
                    'unlock'     => _("Unlocking pages"),
                    'upload'     => _("Uploading zip dumps"),
                    'verify'     => _("Verify the current action"),
                    'viewsource' => _("Viewing the source of pages"),
                    'xmlrpc'	 => _("XML-RPC access"),
                    'zip'        => _("Downloading zip dumps"),
                    'ziphtml'    => _("Downloading html zip dumps")
                    );
        }
        if (in_array($action, array_keys($disallowedActionDescriptions)))
            return $disallowedActionDescriptions[$action];
        else
            return $action;
    }

    function requiredAuthority ($action) {
        $auth = $this->requiredAuthorityForAction($action);
        
        /*
         * This is a hook for plugins to require authority
         * for posting to them.
         *
         * IMPORTANT: this is not a secure check, so the plugin
         * may not assume that any POSTs to it are authorized.
         * All this does is cause PhpWiki to prompt for login
         * if the user doesn't have the required authority.
         */
        if ($this->isPost()) {
            $post_auth = $this->getArg('require_authority_for_post');
            if ($post_auth !== false)
                $auth = max($auth, $post_auth);
        }
        return $auth;
    }
        
    function requiredAuthorityForAction ($action) {
        // FIXME: clean up. 
        // Todo: Check individual page permissions instead.
        switch ($action) {
            case 'browse':
            case 'viewsource':
            case 'diff':
            case 'select':
            case 'xmlrpc':
            case 'search':
                return WIKIAUTH_ANON;

            case 'zip':
            case 'ziphtml':
                if (defined('ZIPDUMP_AUTH') && ZIPDUMP_AUTH)
                    return WIKIAUTH_ADMIN;
                return WIKIAUTH_ANON;

            case 'edit':
                if (defined('REQUIRE_SIGNIN_BEFORE_EDIT') && REQUIRE_SIGNIN_BEFORE_EDIT)
                    return WIKIAUTH_BOGO;
                return WIKIAUTH_ANON;
                // return WIKIAUTH_BOGO;

            case 'create':
                $page = $this->getPage();
                $current = $page->getCurrentRevision();
                if ($current->hasDefaultContents())
                    return $this->requiredAuthorityForAction('edit');
                return $this->requiredAuthorityForAction('browse');

            case 'upload':
            case 'dumpserial':
            case 'dumphtml':
            case 'loadfile':
            case 'remove':
            case 'lock':
            case 'unlock':
                return WIKIAUTH_ADMIN;
            default:
                global $WikiNameRegexp;
                if (preg_match("/$WikiNameRegexp\Z/A", $action))
                    return WIKIAUTH_ANON; // ActionPage.
                else
                    return WIKIAUTH_ADMIN;
        }
    }

    function possiblyDeflowerVirginWiki () {
        if ($this->getArg('action') != 'browse')
            return;
        if ($this->getArg('pagename') != HOME_PAGE)
            return;

        $page = $this->getPage();
        $current = $page->getCurrentRevision();
        if ($current->getVersion() > 0)
            return;             // Homepage exists.

        include('lib/loadsave.php');
        SetupWiki($this);
        $this->finish();        // NORETURN
    }

    function handleAction () {
        $action = $this->getArg('action');
        $method = "action_$action";  

        if (method_exists($this, $method)) {
            $this->{$method}();
        }
        elseif ($page = $this->findActionPage($action)) {
            $this->actionpage($page);
        }
        else {
            $this->finish(fmt("%s: Bad action", $action));
        }
    }
    
    function finish ($errormsg = false) {
        static $in_exit = 0;

        if ($in_exit)
            exit();        // just in case CloseDataBase calls us
        $in_exit = true;

        if (!empty($this->_dbi))
            $this->_dbi->close();
        unset($this->_dbi);


        global $ErrorManager;
        $ErrorManager->flushPostponedErrors();

        if (!empty($errormsg)) {
            PrintXML(HTML::br(),
                     HTML::hr(),
                     HTML::h2(_("Fatal PhpWiki Error")),
                     $errormsg);
            // HACK:
            echo "\n</body></html>";
        }

        Request::finish();
        exit;
    }

    function _deducePagename () {
        if ($this->getArg('pagename'))
            return $this->getArg('pagename');

        if (USE_PATH_INFO) {
            $pathinfo = $this->get('PATH_INFO');
            $tail = substr($pathinfo, strlen(PATH_INFO_PREFIX));

            if ($tail && $pathinfo == PATH_INFO_PREFIX . $tail) {
                return $tail;
            }
        }
        elseif ($this->isPost()) {
            /*
             * In general, for security reasons, HTTP_GET_VARS should be ignored
             * on POST requests, but we make an exception here (only for pagename).
             *
             * The justifcation for this hack is the following
             * asymmetry: When POSTing with USE_PATH_INFO set, the
             * pagename can (and should) be communicated through the
             * request URL via PATH_INFO.  When POSTing with
             * USE_PATH_INFO off, this cannot be done --- the only way
             * to communicate the pagename through the URL is via
             * QUERY_ARGS (HTTP_GET_VARS).
             */
            global $HTTP_GET_VARS;
            if (isset($HTTP_GET_VARS['pagename'])) { 
                return $HTTP_GET_VARS['pagename'];
            }
        }

        /*
         * Support for PhpWiki 1.2 style requests.
         */
        $query_string = $this->get('QUERY_STRING');
        if (preg_match('/^[^&=]+$/', $query_string)) {
            return urldecode($query_string);
        }

        return HOME_PAGE;
    }

    function _deduceAction () {
        if (!($action = $this->getArg('action'))) {
            // Detect XML-RPC requests
            if ($this->isPost()
                && $this->get('CONTENT_TYPE') == 'text/xml') {
                global $HTTP_RAW_POST_DATA;
                if (strstr($HTTP_RAW_POST_DATA, '<methodCall>')) {
                    return 'xmlrpc';
                }
            }

            return 'browse';    // Default if no action specified.
        }

        if (method_exists($this, "action_$action"))
            return $action;

        // Allow for, e.g. action=LikePages
        if ($this->isActionPage($action))
            return $action;

        trigger_error("$action: Unknown action", E_USER_NOTICE);
        return 'browse';
    }

    function _deduceUsername () {
        if ($userid = $this->getSessionVar('wiki_user')) {
            if (!empty($this->_user))
                $this->_user->_authhow = 'session';
            return $userid;
        }
        if ($userid = $this->getCookieVar('WIKI_ID')) {
            if (!empty($this->_user))
                $this->_user->authhow = 'cookie';
            return $userid;
        }
        return false;
    }
    
    function _isActionPage ($pagename) {
        $dbi = $this->getDbh();
        $page = $dbi->getPage($pagename);
        $rev = $page->getCurrentRevision();
        // FIXME: more restrictive check for sane plugin?
        if (strstr($rev->getPackedContent(), '<?plugin'))
            return true;
        if (!$rev->hasDefaultContents())
            trigger_error("$pagename: Does not appear to be an 'action page'", E_USER_NOTICE);
        return false;
    }

    function findActionPage ($action) {
        static $cache;

        if (isset($cache) and isset($cache[$action]))
            return $cache[$action];
        
        // Allow for, e.g. action=LikePages
        global $WikiNameRegexp;
        if (!preg_match("/$WikiNameRegexp\\Z/A", $action))
            return $cache[$action] = false;

        // check for translated version (users preferred language)
        $translation = gettext($action);
        if ($this->_isActionPage($translation))
            return $cache[$action] = $translation;

        // check for translated version (default language)
        global $LANG;
        if ($LANG != DEFAULT_LANGUAGE and $LANG != "en") {
            $save_lang = $LANG;
            //trigger_error("DEBUG: findActionPage() ". DEFAULT_LANGUAGE." calling update_locale()...");
            update_locale(DEFAULT_LANGUAGE);
            $default = gettext($action);
            //trigger_error("DEBUG: findActionPage() ". $save_lang." restoring save_lang, calling update_locale()...");
            update_locale($save_lang);
            if ($this->_isActionPage($default))
                return $cache[$action] = $default;
        }
        else {
            $default = $translation;
        }
        
        // check for english version
        if ($action != $translation and $action != $default) {
            if ($this->_isActionPage($action))
                return $cache[$action] = $action;
        }

        trigger_error("$action: Cannot find action page", E_USER_NOTICE);
        return $cache[$action] = false;
    }
    
    function isActionPage ($pagename) {
        return $this->findActionPage($pagename);
    }

    function action_browse () {
//        $this->buffer_output();
        include_once("lib/display.php");
        displayPage($this);

    }

    function action_verify () {
        $this->action_browse();
    }

    function actionpage ($action) {
//        $this->buffer_output();
        include_once("lib/display.php");
        actionPage($this, $action);
    }

    function action_diff () {
//        $this->buffer_output();
        include_once "lib/diff.php";
        showDiff($this);
    }

    function action_search () {
        // This is obsolete: reformulate URL and redirect.
        // FIXME: this whole section should probably be deleted.
        if ($this->getArg('searchtype') == 'full') {
            $search_page = _("FullTextSearch");
        }
        else {
            $search_page = _("TitleSearch");
        }
        $this->redirect(WikiURL($search_page,
                                array('s' => $this->getArg('searchterm')),
                                'absolute_url'));
    }

    function action_edit () {
//        $this->buffer_output();
        include "lib/editpage.php";

        $e = new PageEditor ($this);

        $e->editPage();
    }

    function action_create () {
        $this->action_edit();
    }
    
    function action_viewsource () {
//        $this->buffer_output();
        include "lib/editpage.php";
        $e = new PageEditor ($this);
        $e->viewSource();
    }

    function action_lock () {
        $page = $this->getPage();
        $page->set('locked', true);
        $this->action_browse();
    }

    function action_unlock () {
        // FIXME: This check is redundant.
        //$user->requireAuth(WIKIAUTH_ADMIN);
        $page = $this->getPage();
        $page->set('locked', false);
        $this->action_browse();
    }

    function action_remove () {
        // FIXME: This check is redundant.
        //$user->requireAuth(WIKIAUTH_ADMIN);
        $pagename = $this->getArg('pagename');
        if (strstr($pagename,_('PhpWikiAdministration'))) {
            $this->action_browse();
        } else {
            include('lib/removepage.php');
            RemovePage($this);
        }
    }


    function action_upload () {
        include_once("lib/loadsave.php");
        LoadPostFile($this);
    }

    function action_xmlrpc () {
        include_once("lib/XmlRpcServer.php");
        $xmlrpc = new XmlRpcServer($this);
        $xmlrpc->service();
    }
    
    function action_zip () {
        include_once("lib/loadsave.php");
        MakeWikiZip($this);
        // I don't think it hurts to add cruft at the end of the zip file.
        echo "\n========================================================\n";
        echo "PhpWiki " . PHPWIKI_VERSION . " source:\n$GLOBALS[RCS_IDS]\n";
    }

    function action_ziphtml () {
        include_once("lib/loadsave.php");
        MakeWikiZipHtml($this);
        // I don't think it hurts to add cruft at the end of the zip file.
        echo "\n========================================================\n";
        echo "PhpWiki " . PHPWIKI_VERSION . " source:\n$GLOBALS[RCS_IDS]\n";
    }

    function action_dumpserial () {
        include_once("lib/loadsave.php");
        DumpToDir($this);
    }

    function action_dumphtml () {
        include_once("lib/loadsave.php");
        DumpHtmlToDir($this);
    }

    function action_loadfile () {
        include_once("lib/loadsave.php");
        LoadFileOrDir($this);
    }
}

//FIXME: deprecated
function is_safe_action ($action) {
    return WikiRequest::requiredAuthorityForAction($action) < WIKIAUTH_ADMIN;
}

