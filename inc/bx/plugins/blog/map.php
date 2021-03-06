<?php
// +----------------------------------------------------------------------+
// | BxCms                                                                |     
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// | See also http://wiki.bitflux.org/License_FAQ                         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+
/*

Add this to your blog.xml in your collection tree.
It will override options from there /inc/bx/config/collection/blog.xml

Here you also have to add you personal google map key for your homepage as parameter
You can get the key here:
http://www.google.com/apis/maps/
    <plugins>
        <extension type="html"/>
        <file preg="#^map/#"/>
        <parameter name="xslt" type="pipeline" value="blog_map.xsl"/>
        <plugin type="blog_map">
        <parameter name="key" value="ABQIAAAA-iuXXHqJ4EHMP0HEaIyFwhQ4zBPGwAlTAZhv0Zs-gp845UxNeRROP25zfiN9Q2s6PBcngC8UVT0hzg"/>
        </plugin>
        <plugin type="navitree"></plugin>
    </plugins>

To activate the map plugin for the blog you need to add the following to your blog.xml in your collection tree.

    <plugins>
        <extension type="html"/>
        <parameter type="pipeline" name="xslt" value="blog.xsl"/>
        <plugin type="blog">
            <parameter name="blog_map" value="true"/>
            <parameter name="blogid" value="$blogid"/>
        </plugin>

        <plugin type="navitree"></plugin>
    </plugins>
    Not needed if you only want to show the map but with this there will be a link under the posts that directly links 
    you to the map and also opens the selected post if your post has geotags.
    
    first you need to create a blog_map.xsl or however you want to name it) in your theme section
    there you need to import /themes/standard/plugins/blog/blog_map.xsl
    
    example: <xsl:import href="../standard/plugins/blog/blog_map.xsl"/>
    
    this should be everything to make it work :)
    
    have fun
    
*/
class bx_plugins_blog_map extends bx_plugin {
    
    static public $instance = array();
    protected $res = array();
    
    protected $db = null;
    protected $tablePrefix = null;
    
    public static function getInstance($mode) {
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_blog_map($mode);
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
    
    
    public function getContentById($path) {
        $tablePrefix = $GLOBALS['POOL']->config->getTablePrefix();
        $db = $GLOBALS['POOL']->db;
        
        $query = "select id, post_author, post_content, post_info, post_title, unix_timestamp(post_date) as unixtime, post_uri as post_uri from ".$tablePrefix."blogposts where post_info != '' and post_content like '%<img%' order by post_date limit 50";
        $res = $db->query($query);
        
        /*$query_images = "select post_content, id 
        from ".$tablePrefix."blogposts where post_content like '%<img%' order by post_date limit 15";
        $res_images = $db->query($query_images);*/
        
        $xml = "";
        if(!MDB2::isError($res)) {
            $xml .= "<locations>";
            $key = $this->getParameter($path,"key");
            $xml .= "<key>";
            $xml .= "<key>".$key."</key>";
            $xml .= "</key>";
            
            if(isset($_GET['id'])) {
                $id = htmlspecialchars($_GET['id']);
                $xml .= "<post>";
                $xml .= "<selectedpost>".$id."</selectedpost>";
                $xml .= "</post>";
            }
            
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $row_replaced = preg_replace('# #', '', $row['post_info']);
                $row_splited = preg_split('#\n#', $row_replaced);
                $row2 = $row_splited[2];
                
                $xml .= "<location>";
                $xml .= preg_replace("#plazelat#", "lat", $row_splited[1]);
                $xml .= preg_replace("#plazelon#", "lon", $row_splited[2]);
                $xml .= preg_replace("#plazename#", "name", $row_splited[3]);
                
                $xml .= "<id>".$row['id']."</id>";
                $xml .= "<title>".$row['post_title']."</title>";
                //link to the post
                $link = 'archive/'. date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).'/'.$row['post_uri'].'.html';
                $xml .= "<link>".$link."</link>";
                
                $xml .= "<author>".$row['post_author']."</author>";
                
                $date = date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']).' '.date('H:i',$row['unixtime']);
                //date('Y',$row['unixtime']).'/'.date('m',$row['unixtime']).'/'.date('d',$row['unixtime']
                $xml .= "<date>".$date."</date>";
                
                //images
                preg_match("#<img.*>#", $row['post_content'], $matches);
                preg_match('#\"(.+?)\"#', $matches['0'], $matches2);
                $image = str_replace('"', "", $matches2['0']);
                
                if(isset($image)) {
                    $xml .= "<image>".$image."</image>";
                }
                if(isset($row['post_content'])) {
                    $xml .= "<content>".$row['post_content']."</content>";
                }
                
                
                $xml .= "</location>";
            }
            $xml .= "</locations>";
        }
        
        $dom = new DomDocument();
        $dom->loadXML($xml);
        return $dom;
    }
}
?>
