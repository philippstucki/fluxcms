<?php
/*
-config.xml
<?xml version="1.0"?>
<bxcms xmlns="http://bitflux.org/config">

    <plugins>
        <parameter name="xslt" type="pipeline" value="blogauthors.xsl"/>
        <plugin type="blogauthors">
            <!-- add blogpath here please if not set xml will not output the uri-->
            <parameter name="blogpath" value="/blog/"/>
            <!-- add blogid here please(needed) if only one blog '1' should be default -->
            <parameter name="blogid" value="1"/>
         </plugin>
    </plugins>
</bxcms>

-XSL
<xsl:template name="content">
    <xsl:for-each select="/bx/plugin[@name='blogauthors']/authors/author">
        <div class="blogauthors">
        <h2 class="post_title"><xsl:value-of select="authorname"/></h2>
        <ul>
        <xsl:for-each select="entry">
            <li>
                <a href="{$webrootLangW}{uri}"><xsl:value-of select="title"/></a>
            </li>
        </xsl:for-each>
        </ul>
        </div>
    </xsl:for-each>
    
</xsl:template>
    
*/
class bx_plugins_blogauthors extends bx_plugin implements bxIplugin {
    
    static public $instance = array();
    protected $res = array();
    
    protected $db = null;
    protected $tablePrefix = null;
    
    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_blogauthors($mode);
        } 
        return self::$instance[$mode];
    }
    
    protected function __construct($mode) {
        $this->tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $this->db = $GLOBALS['POOL']->db;
        $this->mode = $mode;
    }
    
    public function isRealResource($path , $id) {
        return true;
    }
    
    public function getIdByRequest($path, $name = NULL, $ext = NULL) {
        
        return $name.'.'.$this->name;
        
    }
    
    public function getContentById($path, $id){
        
        $blogid = $this->getParameter($path,"blogid");
        $blogPath = $this->getParameter($path,"blogpath");
        
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        
        $db = $GLOBALS['POOL']->db;
        $query = "select post_author from ".$tablePrefix."blogposts where blog_id = $blogid group by post_author";
        
        $res = $db->query($query);
	if(MDB2::isError($res)){
            throw new PopoonDBException($res);
        }
        
        $xml = '<authors>';
        while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $xml .= '<author>';
            $xml .= '<authorname>'.$row['post_author'].'</authorname>';
            foreach($row as $author) {
                $queryPost = "select post_title, post_uri, date_format(post_date, '%Y/%m/%d') as date from ".$tablePrefix."blogposts where blog_id = 1 and post_author = '".$author."'";
                $resPost = $db->query($queryPost);
	        if(MDB2::isError($resPost)){
                    throw new PopoonDBException($resPost);
                }
                while($rowPost = $resPost->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $xml .= '<entry>';
                    $xml .= '<title>'.$rowPost['post_title'].'</title>';
                    $xml .= '<date>'.$rowPost['date'].'</date>';
                    if(isset($blogPath)) {
                        $xml .= '<uri>'.$blogPath.'archive/'.$rowPost['date'].'/'.$rowPost['post_uri'].'.html</uri>';
                    }
                    $xml .= '</entry>';
                }
            }
            $xml .= '</author>';
        }
        $xml .= '</authors>';
        $dom = new DomDocument();
        $dom->loadXML($xml);
        
        return $dom;
    }
    
    public function adminResourceExists($path, $id, $ext=null, $sample = false) {
        return true;
    }
    
}
?>
