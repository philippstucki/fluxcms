<?php

class bx_editors_oneform extends bx_editor implements bxIeditor {    
    
		/** bx_editor::getPipelineParametersById */
		public function getPipelineParametersById($path, $id) {
			return array('pipelineName'=>'oneform');
    }

    public function getDisplayName() {
        return "One Form";
    }
    
        
    public function handlePOST($path, $id, $data) {
     
        $parts = bx_collections::getCollectionAndFileParts($id,"admin");
       return $parts['coll']->handlePostById($parts['rawname'],$data,"FullXML");
    }
    
}

?>
