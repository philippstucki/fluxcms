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

        $xml = '<?xml version="1.0" encoding="iso-8859-1"?>';
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
            max(entries.dc_date) as maxDate
            
            from blogs left join feeds on feeds.blogsID = blogs.ID
            left join entries on entries.feedsID = feeds.ID
            where entries.dc_date > 0 and feeds.section = 'default'
            group by blogs.link
            order by maxDate DESC"
            
            );
            
            $xml .= $this->mdbResult2XML($res,"blog",array("link","title"));
            $xml .= "</blogs>";
        }
        
        $xml .= "</planet>";
        return TRUE;
    }
    
    function getEntries($from,$section,$startEntry) {
          
        
        $cdataFields = array("title","link","description","content_encoded","blog_Title");
        $res = $this->db->query('
        SELECT entries.ID,
        entries.title,
        entries.link,
        entries.description,
        entries.content_encoded,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%e.%c.%Y, %H:%i") as dc_date,
        DATE_FORMAT(DATE_ADD(entries.dc_date, INTERVAL '.($GLOBALS['BX_config']['webTimezone'] ).' HOUR), "%Y-%m-%dT%H:%i") as date_iso,
        
        blogs.link as blog_Link,
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
                $xml .= '<'.$rowField.'>';
                foreach($row as $key => $value) {
                    $xml .= '<'.$key.'>';
                    if (in_array($key,$cdataFields)) {
                        $xml .= '<![CDATA['.$value.']]>';
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
