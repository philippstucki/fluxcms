<?php
/*
*    <?xml version="1.0"?>
*    
*    <bxcms xmlns="http://www.flux-cms.org/config">
*    
*    <plugins>
*        <parameter name="xslt" type="pipeline" value="graph.xsl"/>
*        <plugin type="graph">
*           <parameter name="blogPath" value="/blog/"/>
*			∨f you want to have the graph from diffrent blogs
*			<parameter name="blogPath" value="/blog/;/blog2/"/>
*
*           <parameter name="height" value="300"/>
*           <parameter name="width" value="500"/>
*        </plugin>
*        <plugin type="navitree"/>
*    </plugins>
*    
*    </bxcms>
*/
//require_once 'Image/Graph.php';
class bx_plugins_graph extends bx_plugin implements bxIplugin {    
    
    static public $instance = array();
    protected $res = array();

    protected $db = null;
    protected $tablePrefix = null;

    public static function getInstance($mode) {
        
        if (!isset(self::$instance[$mode])) {
            self::$instance[$mode] = new bx_plugins_graph($mode);
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
		//hmmmmmmmmmm LOOK here ;)
        error_reporting(E_ALL ^ E_NOTICE);
		
		
		$dom = new DomDocument();
		
		$blogpath = $this->getParameter($path,"blogPath",BX_PARAMETER_TYPE_DEFAULT,"/blog/");
		$height = $this->getParameter($path,"height",BX_PARAMETER_TYPE_DEFAULT,"400");
        $width = $this->getParameter($path,"width",BX_PARAMETER_TYPE_DEFAULT,"300");
        $this->getGraph($blogpath, $height, $width);
		
		$xml = '<graphs>';
		
		$xml .= $this->returnLinks($blogpath);
		
		$xml .= '</graphs>';
		$dom->loadXML($xml);
		
		return $dom;
		
    }
    /**
     * Usage example for Image_Graph.
     * 
     * Main purpose: 
     * Show bar chart
     * 
     * Other: 
     * None specific
     * 
     * $Id: plot_bar.php,v 1.4 2005/08/03 21:21:52 nosey Exp $
     * 
     * @package Image_Graph
     * @author Jesper Veggerby <pear.nosey@veggerby.dk>
     */
	public function returnLinks($blogpath) {
		$blogpath = $blogpath.";all";
		
		$blogpaths = explode(";", $blogpath);
		
		$xml = "";
		foreach($blogpaths as $blogpath) {
			$blogpath = preg_replace('#/#', '', $blogpath);
			$xml .= '<graph>';
			$xml .= '<file>dynimages/graph/graph'.$blogpath.'.png</file>';
			$xml .= '</graph>';
		}
		return $xml;
	}
	
	
    public function getGraph($blogpath, $height, $width) {
		$blogpath = $blogpath.";all";
		$blogpaths = explode(";", $blogpath);
		foreach($blogpaths as $blogpath) {
			
			$tableprefix = $GLOBALS['POOL']->config->getTableprefix();
			if($blogpath == 'all') {
				$query = "select DISTINCT count(tag) as c, tag from ".$tableprefix."tags left join ".$tableprefix."properties2tags on ".$tableprefix."tags.id = ".$tableprefix."properties2tags.tag_id left join ".$tableprefix."blogposts on ";
				$x = 0;
				foreach($blogpaths as $path) {
					if($x >= 1) {
						$query .= " or ";
					}
					
					$query .= $tableprefix."properties2tags.path = concat('".$path."',".$tableprefix."blogposts.post_uri,'.html')";
					$x++;
				}
				
				$query .= "where ".$tableprefix."blogposts.post_date > '".date('Y-m-d H:i:s', time()-30*24*60*60)."' group by tag order by c";
				
			} else {
				$query = "select DISTINCT count(tag) as c, tag from ".$tableprefix."tags left join ".$tableprefix."properties2tags on ".$tableprefix."tags.id = ".$tableprefix."properties2tags.tag_id left join ".$tableprefix."blogposts on ".$tableprefix."properties2tags.path = concat('".$blogpath."',".$tableprefix."blogposts.post_uri,'.html') where ".$tableprefix."blogposts.post_date > '".date('Y-m-d H:i:s', time()-30*24*60*60)."' group by tag order by c";
			}
			
			$res = $GLOBALS['POOL']->db->query($query);
			
			
			// create the graph
			$Graph =& Image_Graph::factory('graph', array(array('width' => $width, 'height' => $height)));
			// add a TrueType font
			$Font =& $Graph->addNew('font', BX_PROJECT_DIR.'inc/bx/helpers/graph/Verdana.ttf');
			// set the font size to 11 pixels
			$Font->setSize(9);
			
			$Graph->setFont($Font);
			$blogpath = preg_replace('#/#', '', $blogpath);
			$Graph->add(
				Image_Graph::vertical(
					Image_Graph::factory('title', array($blogpath, 12)),        
					Image_Graph::horizontal(
						$Plotarea = Image_Graph::factory('plotarea', array('category', 'axis', 'horizontal')),
						$Legend = Image_Graph::factory('legend'),
						100
					),
					5
				)
			);  
			
			//&$Plotarea2 = Image_Graph::factory('plotarea', array('category', 'axis', 'horizontal')),
			$Legend->setPlotarea($Plotarea);        
			
			// create the dataset
			$Dataset =& Image_Graph::factory('dataset'); 
			
			
			while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
				if(!empty($row['tag'])) {
					$Dataset->addPoint($row['tag'], $row['c']);
					//$Dataset =& Image_Graph::factory('function', array(0, $row['c'], 'calc', 100));
				}
			}
			// create the 1st plot as smoothed area chart using the 1st dataset
			$Plot =& $Plotarea->addNew('bar', array(&$Dataset));
			// set a line color
			$Plot->setLineColor('gray');
			
			// set a standard fill style
			$Plot->setFillColor('blue@0.2');
					
			if(!is_dir(BX_PROJECT_DIR.'dynimages/graph/')) {
			   mkdir(BX_PROJECT_DIR.'dynimages/graph/');
			}
				
			// output the Graph
			if(!file_exists(BX_PROJECT_DIR.'dynimages/graph/graph'.$blogpath.'.png')) {
				$Graph->done(
					array('filename' => BX_PROJECT_DIR.'dynimages/graph/graph'.$blogpath.'.png') 
				);
			} else {
				//unlink(BX_PROJECT_DIR.'dynimages/graph/graph'.$blogpath.'.png');
				$Graph->done(
					array('filename' => BX_PROJECT_DIR.'dynimages/graph/graph'.$blogpath.'.png') 
				);
			}
			
		}
    }
}
?> 
