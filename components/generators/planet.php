<?php


class popoon_components_generators_planet extends popoon_components_generator {
    
    var $maxBlogTitleLength  = 35;
    
    function __construct (&$sitemap) 
    {
        parent::__construct($sitemap);
    }
    
    function init($attribs)
    {
        parent::init($attribs);
       // $this->db = $this->getParameterDefault("db");
        
    }    
    
    function DomStart(&$xml)
    {
        include_once("MDB2.php");
        if (!isset($GLOBALS['BX_config']['webTimezone'])) {
            $GLOBALS['BX_config']['webTimezone'] = $GLOBALS['BX_config']['serverTimezone'];
        }
        if($GLOBALS['BX_config']['webTimezone'] < 0) {
            $TZ = sprintf("-%02d:00",abs($GLOBALS['BX_config']['webTimezone']));
        } else {
            $TZ = sprintf("+%02d:00",abs($GLOBALS['BX_config']['webTimezone']));
        }
        
        $startEntry = $this->getParameterDefault("startEntry");
        $search = $this->getParameterDefault("search");
        
        
        $this->db = MDB2::Connect($GLOBALS['BX_config']['dsn']);

        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<planet>';
        $xml .= '<search>';
        if ($search) {
            if (strlen($search) <= 3) {
                $where = " where content_encoded LIKE '%$search%' or entries.description LIKE '%$search%' or entries.title LIKE '%$search%' ";
            } else {
                $where = " where match(content_encoded , entries.description, entries.title ) against('". $search . "') ";
            }
            
            $xml .= '<string>'.$search .'</string>';
           
        } else {
            $where = "where  1=1 ";
        }
        
        $from = 'from entries
        left join feeds on entries.feedsID = feeds.ID
        left join blogs on feeds.blogsID = blogs.ID
        ';

        $this->db->loadModule("extended");
        $count = $this->db->extended->getOne('select count(entries.ID) ' . $from . $where ." and feeds.section = 'default'");
        
        $xml .= '<count>'.$count.'</count>';
        $xml .= '<start>'.$startEntry.'</start>';
        $xml .= '</search>';
        switch (substr($this->sitemap->uri,0,3)) {
            case "rdf":
            case "rss":
            case "ato":
            $xml .= $this->getEntries( $from.$where, "default",0);    
            break;
            default:
            $xml .= $this->getEntries( $from.$where, "default",$startEntry);    
            $xml .= $this->getEntries( $from." where 1=1", "releases",0);
        }
            
        
        
        
        if ($this->getParameterDefault("feedsList") == "yes") {
            $xml .= '<blogs>';
            
            $res = $this->db->query("
            select 
            blogs.link as link,
            blogs.title as title,
	    blogs.dontshowblogtitle  as dontshowblogtitle,
            blogs.author as author,
            unix_timestamp(max(entries.dc_date)) as maxDate,
            unix_timestamp(date_sub(now(), INTERVAL 100 DAY)) as border

            from blogs left join feeds on feeds.blogsID = blogs.ID
            left join entries on entries.feedsID = feeds.ID
            where entries.dc_date > 0 and feeds.section = 'default'
            group by blogs.link
            order by maxDate DESC"
            
            );
            
            $xml .= $this->mdbResult2XML($res,"blog",array("link","title","author"));
            $xml .= "</blogs>";
        }
        $delicious = $this->getParameterDefault("deliciousRss");
        if ($delicious) {
            $simplecache = new popoon_helpers_simplecache();

            $simplecache->cacheDir = BX_TEMP_DIR;
            $uri = 'http://del.icio.us:80/rss/'.$delicious;

            $t = $simplecache->simpleCacheHttpRead($uri,1600);
            
            $deldom = new domdocument();
	    $t = iconv("UTF-8","UTF-8//IGNORE",$t);            
            if ($deldom->loadXML($t)) {
                $xml .= preg_replace("#<\?xml[^>]*\?>#","",$deldom->saveXML());
            }

        }
        $xml .= "</planet>";
        return TRUE;
    }
    
    function getEntries($from,$section,$startEntry) {
          
        
        $cdataFields = array("title","link","description","content_encoded","blog_title","blog_author");
        $res = $this->db->query('
        SELECT entries.ID,
        entries.title,
        entries.link,
        entries.description,
        entries.content_encoded,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%e.%c.%Y, %H:%i") as dc_date,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%Y-%m-%dT%H:%i+00:00") as date_iso,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%a, %d %b %Y %T +0000") as date_rfc,
        
        blogs.link as blog_Link,
	blogs.author as blog_Author,
	blogs.dontshowblogtitle as blog_dontshowblogtitle,
        if(length(blogs.title) > '. ($this->maxBlogTitleLength + 5) .' , concat(left(blogs.title,'. ($this->maxBlogTitleLength ) .')," ..."), blogs.Title) as blog_Title
        ' . $from . ' and feeds.section = "'.$section.'"
        order by entries.dc_date DESC 
        limit '.$startEntry . ',10');

	$xml = '<entries section="'.$section.'">';
        $xml .= $this->mdbResult2XML($res,"entry",$cdataFields);
        $xml .= '</entries>';
        return $xml;
        
    }
    
    function mdbResult2XML ($res, $rowField, $cdataFields = array()) {
        $xml = "";
        if(!MDB2::isError($res)) {
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                if (empty($row['content_encoded'])) {
                    $row['content_encoded'] = utf8_encode($row['description']);
                }
                $xml .= '<'.$rowField.'>';
                foreach($row as $key => $value) {
                    $xml .= '<'.$key.'>';
                    if (in_array($key,$cdataFields)) {
			
			  $value= preg_replace('#(<[^>]+[\s\r\n\"\'])on[^>]*>#iU',"$1>",$value);
                        $xml .= '<![CDATA['.str_replace("<![CDATA[","",str_replace("]]>","",str_replace("pre>","code>",($value)))).']]>';
                    } else {
                        $xml .= $value;
                    }
                    $xml .= '</'.$key.'>';
                }
                $xml .= '</'.$rowField.'>';
                
                
            }
            
        } 
        else {
            
            $xml = "<!-- \n" .$res->getMessage() . "\n". $res->getUserInfo() ." -->";
        }
        return $xml;
    }
}


?>
