<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001,2002,2003,2004 Bitflux GmbH                       |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id$



/**
* Outputs an xsl:fo document as pdf
*
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id$
* @package  popoon
*/
class popoon_components_serializers_fo2pdf extends popoon_components_serializer {
    
    var $XmlFormat = "XmlString";
    var $contentType = "application/pdf";
    
    function __construct ($sitemap) {
        
        $this->sitemap = $sitemap;
    }
    
    public function init($attribs) {
        parent::init($attribs);
    }
    
    public function DomStart(&$xml) {
        $cmd = $this->getAttrib("commandline");
        
        if ($cmd) {
            $this->doOnCommandLine($xml,$cmd);
        } else {
            $this->doWithFo2Pdf($xml);
        }
    }
    
    protected function doOnCommandLine ($xml, $cmd) {
        $foname = tempnam ($this->sitemap->cacheDir, "fo2pdf.fo.");
        $pdfname = tempnam ($this->sitemap->cacheDir, "fo2pdf.pdf.");
        
        file_put_contents($foname,$xml);
        if ($conf = $this->getParameterDefault("configFile")) {
            $cmd .= ' -c '.$conf;
        }
        $returnstr =  exec(escapeshellcmd($cmd . " $foname $pdfname"),$error);
        if ($error) {
//            print $error;
        }
        header("Content-Length: ".filesize($pdfname));
        readfile($pdfname);
        
        unlink($foname);
        unlink($pdfname);
        
    }
    
    protected function doWithFo2Pdf($xml) {
        
        require_once("XML/fo2pdf.php");
        //make a pdf from simple.fo and save the pdf in a tmp-folder
        $fop = new xml_fo2pdf();
        
        // the following 2 lines are the default settins, so not
        // necessary here, but you can set it to other values        
        $fop->setRenderer("pdf");
        
        //If you want other fonts in your PDF, you need to declare them in a
        // config file. Declare here the path to this file [optional]. 
        // More information about fonts and fop on the apache-fop webpage.
        
        if (PEAR::isError($error = $fop->runFromString($xml)))
        {
            die("FOP ERROR: ". $error->getMessage());
        }
        
        //print pdf to the outputbuffer,
        // including correct Header ("Content-type: application/pdf")
        
        $fop->printPDF();
        //delete the temporary pdf file
        $fop->deletePDF();
    }
    
}


?>
