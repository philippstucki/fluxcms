<?php


class popoon_helpers_mimetypes {
    
    
    function getFromFileLocation($src) {
        $extension = substr($src,strrpos($src,".")+1);
        
        switch ($extension) {
            case "gif":
            return "image/gif";
            case "jpg":
            case "jpeg":
            return "image/jpeg";
            case "png":
            return "image/png";
            case "css":
            return "text/css";
            case "xml":
            case "xsl":
            case "xsd":
            case "rng":
            case "tal":
            return "text/xml";
            case "js":
            return "text/javascript";
            case "html":
            case "htm":
            case "xhtml":
            return "text/html";
            case "txt":
            return "text/plain";
            case "pdf":
            return "application/pdf";
            
            default:
            if (file_exists($src)) {
                $m =  `file -ib $src`;
                return $m;
            } else {
                return "text/plain";
            }
        }
        
        
    }
    
}
