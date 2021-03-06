<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Liip AG                                           |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+
//
// $Id$





print "<pre/>";
$db = $GLOBALS['POOL']->dbwrite;

// check for last version
$res = $db->query("select value from  ".$tablePrefix."options where name = 'lastdbversion'");


if (MDB2::isError($res)) {
    // if no such table, create it
    if ($res->code == -18) {
        $lastVersion = 3779;

    } else {
        printError($res);
    }
} else {
    $lastVersion = $res->fetchOne(0,0);
    if (!$lastVersion) {
        print "no lastdbversion found in option table, try to get it from _state \n";
        $lastVersion = $db->queryOne("select value from  ".$tablePrefix."_state where name = 'lastdbversion'");
        if (MDB2::isError($lastVersion) || !$lastVersion) {
            $lastVersion = 3779;
        }
        doQuery("insert into ".$tablePrefix."options (name, value) VALUES ('lastdbversion', $lastVersion)");
    }

}
/** add user_gid into users table **/
if ($lastVersion < 3780) {
  addCol('users','user_gid','int',"'1'");
  addCol('bloglinks','rel','varchar(40)',"''",false);
  updateLastVersion(3780);
}

if ($lastVersion < 3839) {

    $res = doQuery("CREATE TABLE  `".$tablePrefix."options` (
    `id` int(11) NOT NULL auto_increment,
    `name` varchar(100) NOT NULL default '',
    `value` varchar(200) NOT NULL default '',
    `isarray` tinyint(4) NOT NULL default '0',
    PRIMARY KEY  (`id`)
    ) TYPE=MyISAM;",false);
    if (MDB2::isError($res)) {
        if ($res->code == -5) {
            print $tablePrefix."options already exists, moving forward \n";

        } else {
            printError($res);
        }
    }
    doQuery("insert into ".$tablePrefix."options (name, value) VALUES ('lastdbversion', $lastVersion)");
    updateLastVersion(3839);


}

if ($lastVersion < 3991) {
     addCol('users','user_tmphash','varchar(32)',"''");
     updateLastVersion(3991);
}


if ($lastVersion < 4210) {
    addCol('properties','value_date','DATETIME');
    addCol('properties','value_int','INT');
    doQuery("ALTER TABLE `{tablePrefix}properties` ADD FULLTEXT (
`value`
);",false);
 updateLastVersion(4210);
}

if ($lastVersion < 4218) {

    $res = doQueryTable("CREATE TABLE `".$tablePrefix."properties2tags` (
    `id` int(11) NOT NULL auto_increment,
    `path` varchar(255) NOT NULL default '',
    `tag_id` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`),
    KEY `path` (`path`),
    KEY `tag_id` (`tag_id`)
    )","properties2tags");

    $res = doQueryTable("CREATE TABLE `".$tablePrefix."tags` (
    `id` int(11) NOT NULL auto_increment,
    `tag` varchar(255) NOT NULL default '',
    PRIMARY KEY  (`id`),
    KEY `tag` (`tag`)
    )","tags");
    updateLastVersion(4218);
}

if ($lastVersion < 4219) {
    doQuery("ALTER TABLE `{tablePrefix}properties` ADD INDEX  `name-ns` ( `name` , `ns` ) ",false);
    updateLastVersion(4219);
}

if ($lastVersion < 4233) {
    addCol('bloglinks','description','TEXT');
    updateLastVersion(4233);
}

if ($lastVersion < 4265) {
    addCol('bloglinks','date','DATETIME');
    updateLastVersion(4265);
}


if ($lastVersion < 4284) {
    addCol('blogposts','post_content_extended','TEXT NOT NULL');
    addCol('blogposts','post_content_summary','TEXT NOT NULL');
    updateLastVersion(4284);
}


if ($lastVersion < 4328) {
    doQuery("ALTER TABLE `{tablePrefix}blogposts` DROP `post_keywords`",false);
    updateLastVersion(4328);
}

if ($lastVersion < 4534) {
    addCol('blogcomments','comment_rejectreason','TEXT');
    updateLastVersion(4534);
}

if ($lastVersion < 4841) {
    doQuery("ALTER TABLE `{tablePrefix}options` CHANGE `value` `value` TEXT NULL ",false);
    updateLastVersion(4841);
}


if ($lastVersion < 5084) {
    addCol('blogposts','post_lang','CHAR(2)');
    updateLastVersion(5084);
}


if ($lastVersion < 5163) {
 doQuery("ALTER TABLE `{tablePrefix}bloglinks` CHANGE `rel` `rel` VARCHAR( 200 )  NULL",false);
 doQuery("ALTER TABLE `{tablePrefix}bloglinks` CHANGE `bloglinkscategories` `bloglinkscategories` INT( 11 ) NULL DEFAULT '0'",false);
 doQuery("ALTER TABLE `{tablePrefix}bloglinks` CHANGE `rang` `rang` INT( 11 ) DEFAULT '0'",false);
  updateLastVersion(5163);
}
if ($lastVersion < 5266) {
    addCol('blogcomments','comment_hash','VARCHAR( 33 ) NULL');
    updateLastVersion(5266);
}

if ($lastVersion < 5282) {
    addCol('blogposts','post_info', 'TEXT NULL');
    updateLastVersion(5282);
}

if ($lastVersion < 5283) {
    addCol('users','plazes_username', 'VARCHAR(40) NULL');
    addCol('users','plazes_password', 'VARCHAR(32) NULL');
    updateLastVersion(5283);
}

if ($lastVersion < 5349) {
    doQuery("ALTER TABLE `{tablePrefix}blogposts` CHANGE `id` `id` INT( 10 ) NOT NULL AUTO_INCREMENT",false);
    updateLastVersion(5349);
}

if ($lastVersion < 5390) {
    addCol('users','user_adminlang', 'VARCHAR(5) NULL');
    updateLastVersion(5390);
}

if ($lastVersion < 5522) {
    doQuery("update  `{tablePrefix}blogposts` set post_comment_mode = 99 where post_comment_mode = ". $GLOBALS['POOL']->config->blogDefaultPostCommentMode,false);
    updateLastVersion(5522);
}

if ($lastVersion < 5643) {
    addCol('blogcomments','comment_notification', "tinyint(4) NOT NULL default '0'");
    addCol('blogcomments','comment_notification_hash', "varchar(32) NULL");
    updateLastVersion(5643);
}


if ($lastVersion < 5673) {
    doQuery("ALTER TABLE `{tablePrefix}blogcomments` CHANGE  `comment_notification` `comment_notification` TINYINT( 4 ) DEFAULT '0'",false);
    updateLastVersion(5673);
}

if ($lastVersion < 5718) {
    doQuery("update {tablePrefix}blogposts set post_status = 1 where post_status = 0", false);
    updateLastVersion(5718);
}

if ($lastVersion < 5812) {
    addCol('bloglinks','rss_link', "varchar(200) NULL");
    updateLastVersion(5812);
}


if ($lastVersion < 5849) {
  doQuery("ALTER TABLE `{tablePrefix}blogposts` DROP `post_category`, DROP `post_karma`;", false);
  addCol('blogposts','post_guid_version',"TINYINT DEFAULT '2' NOT NULL");
  doQuery("update `{tablePrefix}blogposts` set post_guid_version = 1, changed = changed",false);
  updateLastVersion(5849);
}

if ($lastVersion < 6247) {
    addCol('blogposts','post_expires',"DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
    updateLastVersion(6286);
}

if ($lastVersion < 6286) {
    doQuery("ALTER TABLE `{tablePrefix}blogposts` CHANGE `post_expires` `post_expires` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",false);
    updateLastVersion(6286);
}

if ($lastVersion < 6328) {

    doQuery("ALTER TABLE `{tablePrefix}properties` CHANGE `name` `name` VARCHAR( 36 ) NOT NULL",false);
    doQuery("ALTER TABLE `{tablePrefix}properties` CHANGE `ns` `ns` VARCHAR( 40 ) DEFAULT 'DAV:' NOT NULL",false);
    updateLastVersion(6328);
}

if ($lastVersion < 6469) {
    doQuery("ALTER TABLE `{tablePrefix}bloglinks` CHANGE `changed` `changed` TIMESTAMP NOT NULL",false);
     updateLastVersion(6469);
}

if ($lastVersion < 6478) {
    addCol('blogposts','blog_id',"INT NOT NULL DEFAULT 1");
    updateLastVersion(6478);
}

if ($lastVersion < 6504) {
    addCol('bloglinks','blog_id',"INT NOT NULL DEFAULT 1");
    addCol('bloglinkscategories','blog_id',"INT NOT NULL DEFAULT 1");
    updateLastVersion(6504);
}

if ($lastVersion < 6520) {
    addCol('blogcategories','blog_id',"INT NOT NULL DEFAULT 1");
    updateLastVersion(6520);
}


if ($lastVersion < 6595) {
    $res = doQueryTable("
    CREATE TABLE `".$tablePrefix."openid_uri` (
      `id` int(11) NOT NULL auto_increment,
      `date` date NOT NULL default '0000-00-00',
      `uri` varchar(255) NOT NULL default '',
      PRIMARY KEY  (`id`)
      )",'openid_uri');
      updateLastVersion(6595);
      addCol("blogcomments","openid"," TINYINT( 4 ) DEFAULT '0' NOT NULL");
}
if ($lastVersion < 6705) {

        doQuery("ALTER TABLE `{tablePrefix}blogposts` ADD INDEX ( `post_status` )",false);
        doQuery("ALTER TABLE `{tablePrefix}blogposts` ADD INDEX ( `blog_id` ) ",false);
        updateLastVersion(6705);
}

if ($lastVersion < 6760) {
    doQuery("ALTER TABLE `{tablePrefix}blogcomments` ADD INDEX ( `comment_status` )",false);
    updateLastVersion(6760);
}
if ($lastVersion < 6991) {
    addCol("blogcomments","comment_username"," VARCHAR( 100 ) NOT NULL ","", false);
    updateLastVersion(6991);
}

if ($lastVersion < 7312) {
    $res = doQueryTable("
   CREATE TABLE `".$tablePrefix."sidebar` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `content` text NOT NULL,
  `sidebar` int(11) NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `isxml` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ",'sidebar');


    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'links', '<bloglinks/>', 2, 1,  1);",false);
    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'buttons', '<buttons/>', 2, 3,  1);",false);
    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'html', '<h3 class=\"blog\">More HTML ideas here</h3>\n', 0, 0,1);",false);
    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'html2', '<h3 class=\"blog\">\nPlace your content here\n</h3>', 0, 1, 1);",false);
    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'livesearch', '<livesearch/>', 2, 0,  1);",false);
    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'del.icio.us', '<delicious link=\"tag/freeflux/\"/>', 0, 2, 1);",false);
    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'login', '<login/>', 2, 4, 1);",false);
    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'archive', '<archive/>', 2, 2, 1);",false);
    doQuery("INSERT INTO `{tablePrefix}sidebar` ( `name`, `content`, `sidebar`, `position`,  `isxml`) VALUES ( 'categories', '<categories/>', 1, 0, 1);",false);
      updateLastVersion(7312);
}

if ($lastVersion < 7720) {
    $res = doQueryTable("
   CREATE TABLE `".$tablePrefix."userauthservices` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `service` varchar(50) NOT NULL default '',
  `account` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`)
)",'userauthservices');

      updateLastVersion(7720);
}

if ($lastVersion < 8453) {
    $res = doQueryTable("
   CREATE TABLE `".$tablePrefix."comments` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `comment_posts_id` int(11) NOT NULL default '0',
  `comment_author` tinytext NOT NULL,
  `comment_author_email` varchar(100) NOT NULL default '',
  `comment_author_url` varchar(100) NOT NULL default '',
  `comment_author_ip` varchar(100) NOT NULL default '',
  `comment_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `comment_content` text NOT NULL,
  `comment_karma` int(11) NOT NULL default '0',
  `changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `comment_type` varchar(20) NOT NULL default '',
  `comment_status` tinyint(4) NOT NULL default '1',
  `comment_rejectreason` text,
  `comment_hash` varchar(33) default NULL,
  `comment_notification` tinyint(4) default '0',
  `comment_notification_hash` varchar(32) default '',
  `openid` tinyint(4) NOT NULL default '0',
  `comment_username` varchar(100) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `comment_posts_id` (`comment_posts_id`),
  KEY `comment_status` (`comment_status`)
  )",'comments');

      updateLastVersion(8453);
}

if ($lastVersion < 8633) {
    $res = doQueryTable("
  CREATE TABLE `".$tablePrefix."openid_profiles` (
  `id` int(11) NOT NULL auto_increment,
  `persona` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `postal` varchar(10) NOT NULL,
  `gender` varchar(2) NOT NULL,
  `country` varchar(255) NOT NULL,
  `timezone` varchar(255) NOT NULL,
  `lang` varchar(10) NOT NULL,
  `standard` varchar(2) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
  )",'openid_profiles');

      updateLastVersion(8633);
}


if ($lastVersion < 8823) {

    doQuery("UPDATE {$tablePrefix}blogposts  LEFT JOIN {$tablePrefix}blogposts2categories ON {$tablePrefix}blogposts.id= {$tablePrefix}blogposts2categories.blogposts_id SET {$tablePrefix}blogposts.post_status = 4 WHERE {$tablePrefix}blogposts2categories.blogposts_id IS NULL",false);
    updateLastVersion(8823);

}

if ($lastVersion < 9242) {
	$res = doQuery("
CREATE TABLE `".$tablePrefix."history_diff` (
  `diff_id` bigint(20) NOT NULL auto_increment,
  `diff_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `diff_path` varchar(255) NOT NULL default '',
  `diff_value` text NOT NULL,
  PRIMARY KEY  (`diff_id`),
  KEY diff_path (diff_path),
  KEY diff_timestamp (diff_timestamp)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;", false);

	updateLastVersion(9242);
}

if ($lastVersion < 10145) {
    addCol("blogposts","post_author_id"," int(11) NOT NULL ","", false);
    updateLastVersion(10145);
}


if ($lastVersion < 11299) {
    doQuery("ALTER TABLE `{tablePrefix}properties` ADD INDEX  `value-index`  (`value`(100)); ",false);
    updateLastVersion(11299);
}

if ($lastVersion < 11784) {
    include("switchdbutf8.php");
    updateLastVersion(11784);
}

// delete config files
@unlink(BX_TEMP_DIR."/config.inc.php");
@unlink(BX_TEMP_DIR."/config.inc.php.post");

print "DB up-to-date \n";


/** FUNCTIONS **/

function doQueryTable($query,$tableName) {
    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    $res = doQuery($query,false);
    if (MDB2::isError($res)) {
        if ($res->code == -5) {
            print $tablePrefix.$tableName ." already exists, moving forward \n";

        } else {
            printError($res);
        }
    }

}

function doQuery($query,$fatal=true) {
    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    $db = $GLOBALS['POOL']->db;
    $query = str_replace("{tablePrefix}",  $tablePrefix,$query);
    print "Do " . $query ."\n\n";
    $res = $db->query($query);
    if ($fatal) {
        printError($res);
    } else {
        return $res;
    }
}

function printError($res, $fatal = true) {
    if (MDB2::isError($res)) {
        print $res->message ."\n";
        print $res->userinfo ."\n";
        if ($fatal) {
        	die();
	}
    }
}

function updateLastVersion($version) {
    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    $query = "update  ".$tablePrefix."options set value = $version where name = 'lastdbversion'";

    doQuery($query);
}

function addCol($table,$name,$type,$default = null,$fatal = true) {
    $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
    $query = "ALTER TABLE `".$tablePrefix."$table` ADD `$name` $type";
    if ($default) {
        $query .= " DEFAULT $default";
    }
    $res = doQuery($query, false);

    if (MDB2::isError($res)) {
        if ($res->code == -1) {
            print "  '$name' already exists in '".$tablePrefix."$table' (non fatal) \n\n";
        } else if ($res->code == -18) {
            print "'". $tablePrefix."$table' doesn't exist (non fatal) \n\n";
        } else {
            printError($res,$fatal);
        }
    }
}

