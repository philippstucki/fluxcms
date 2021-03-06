<?php

class bx_resources_text_html extends bx_resource {
    
    protected $fulluri = "";
    protected $mimetype = "text/html";
    protected $id = "";
    
    protected $localName = "";
    protected $lang = "";
    
    public function __construct($id, $new = false) {
        $this->mimetype = "text/html";
        $this->fulluri = $id;
        $this->props['fileuri'] = BX_DATA_DIR.$id;
        $this->id = $id;
        if ($new === true) {
          $this->mock = true;
        } 
        //TODO, check if file really exists...
        
    }
        
    public function getContentUri() {
        return BX_DATA_DIR.$this->fulluri;
      
    }
    
    public function getContentUriSample() {
        $theme = $GLOBALS['POOL']->config->getConfProperty('theme');
        
        if (isset($_REQUEST['template'])) {
            
            $template = sprintf("%s%s/templates/%s", BX_THEMES_DIR, $theme, $_REQUEST['template']);
            if (file_exists($template)) {
                return $template;
            }
            
        }
        
        $template = sprintf("%s%s/templates/%s", BX_THEMES_DIR, $theme, "default.xhtml");
        if (file_exists($template)) {
            return $template;
        }
        return BX_LIBS_DIR.'doctypes/default.xhtml';
        
    }
    
    public function create() {
        $this->init();
    }
    
    protected function init() {
        $this->setProperty("mimetype","text/html");
        $this->setProperty("output-mimetype","text/html");
        $this->setProperty("parent-uri",bx_collections::getCollectionUri($this->id));
        $this->setProperty("display-name",$this->getFileName());
        $this->setProperty("display-order",99);
        
        //parent::init();
    }
    
     public function getDisplayName() {
        $dn = $this->getProperty("display-name");
        if ($dn) {
            return $dn;
        }
        return $this->getFileName();
           
     }
     
     protected function parseName() {
         if (!$this->localName) {
         $p = bx_collections::getCollectionUriAndFileParts($this->getID());
         $this->collUri = $p['colluri'];
         $this->baseName = $p['rawname'];
         if (preg_match("#(.*)\.([a-z]{2})$#",$p['name'],$matches)) {
             $this->localName = $matches[1];
             $this->lang = $matches[2];
         }
         else {
            $this->localName = $p['name'];
            $this->lang = "";
         }
         }
     
    }
    
    public function getBaseName() {
        $this->parseName();
        return$this->baseName;
    }
     
     
     public function getOutputMimeType() {
         return "text/html";
     }
     
    public function getMimeType() {
         return "text/html";
     }
     
    public function getEditors() {
    	
    	$perm = bx_permm::getInstance();
    	$localUri = substr($this->id, 0, strrpos($this->id, '/')+1);
    	
    	$e = array();
 		if($perm->isAllowed($localUri,array('xhtml-back-edit_oneform'))) {
        	$e[] = "oneform";
    	}	
 		$e[] = "ooo";
    	
 		if(popoon_classes_browser::supportedByFCK() && $perm->isAllowed($localUri,array('xhtml-back-edit_fck'))) {
            array_unshift($e, "fck");
        }   
        
        if (popoon_classes_browser::isMozilla()) {
        	if($perm->isAllowed($localUri,array('xhtml-back-edit_kupu'))) {
        		array_unshift($e, 'kupu');
    		}	
        }
        else if (popoon_classes_browser::isMSIEWin()) {
        	if($perm->isAllowed($localUri,array('xhtml-back-edit_kupu'))) {
        		array_unshift($e, 'kupu');
    		}	
        } 
        
        $a = $GLOBALS['POOL']->config->getConfProperty('assets');
        if (!empty($a)) {
            array_push($e, 'assets');
        }
        
        $v = $GLOBALS['POOL']->config->getConfProperty('versioning');
        if (!empty($v)) {
            array_push($e,'versioning');
        }
        
        return $e;
     }
     
     public function getLanguage() {
          $this->parseName();
         return $this->lang;  
     }
     
     
     public function getFileName() {
        $this->parseName();
         return $this->localName;  
         
     }
     
     
     
     public function addResource($name, $parentUri, $options=array()) {
         $perm = bx_permm::getInstance();
         $localUri = substr($this->id, 0, strrpos($this->id, '/')+1);
         if (!$perm->isAllowed($localUri,array('xhtml-back-edit_'.$options['editor']))) {
             throw new BxPageNotAllowedException();
         }    
         
         $template = (isset($options['template'])) ? $options['template'] : 'default.xhtml';
         $editor = isset($options['editor']) ? '&editor='.$options['editor'] : '';
         $location = sprintf("%sadmin/edit%s?template=%s%s", BX_WEBROOT, $this->fulluri, $template, $editor);
         
         header("Location: $location");
         exit(0);
         
     }
     
     public function delete() {
         if (file_exists($this->props['fileuri'])) {
             
            @unlink($this->props['fileuri']);
            if (bx_resourcemanager::removeAllProperties($this->id)) {
                return true;
            }
         }
         
         return false;
     }
        

    public function onSave($old) {
        //filter tbody
        if (!empty($_GET['editor']) && $_GET['editor'] == 'fck') {
            $html = file_get_contents($this->props['fileuri']);
            if (strpos($html,'<tbody>') !== false) {
                $html = str_replace(array('<tbody>','</tbody>'),'',$html);
                file_put_contents($this->props['fileuri'],$html);
            }
        }
        
        //versioning
        $vconfig = $GLOBALS['POOL']->config->getConfProperty('versioning');
        
        if ($vconfig && !empty($vconfig)) {
            $vers = bx_versioning::versioning($vconfig);

            if ($vers) {
            	$vers->setOld($old);
                $vers->commit($this->props['fileuri'], $this->fulluri, '');
            }
        }
        
		
        
        bx_metaindex::callIndexerFromFilename($this->props['fileuri'],$this->id);
    }
    
    
     public function getOutputUri() {
         
         if (!isset($this->outputUri)) {
             
             $this->parseName();
             if ($this->lang !=  BX_DEFAULT_LANGUAGE) {
                 $this->outputUri = "/".$this->lang;
             } else {
                 $this->outputUri = "";
             }
             $this->outputUri .= $this->collUri.$this->getFileName() .".html";
             
         }
         
         return $this->outputUri;
     }
     /* THIS ISN'T PERFECT 
     
     we should use new bx_resources_text_html($to) or something like that
     and moreover check for the right extension...
     but basically it works for now
     */
     public function copy($to, $move = false) {
         // if enddestination is a directory, adjust accordingly
         if (is_dir(BX_DATA_DIR.$to)) {
            $to = $to .'/'.basename($this->props['fileuri']);   
         }
            if (!copy($this->props['fileuri'],BX_DATA_DIR.$to)) {
                return false;
            }
            //copy properties           
            foreach (bx_resourcemanager::getAllProperties($this->id) as $key => $value) {
                bx_resourcemanager::setProperty($to,$value['name'],$value['value'],$value['namespace']);
            }
            if (!$move) {
                bx_resourcemanager::setProperty($to,"display-order","0");
            }
            $newcolluri = bx_collections::getCollectionUri($to);
            
            
            bx_resourcemanager::setProperty($to,"parent-uri",$newcolluri);
            
            return true;
     }
     
     public function getDisplayOrder() {
        return $this->getProperty("display-order");
    }
     
}
