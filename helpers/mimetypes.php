<?php


class popoon_helpers_mimetypes {


    static function getFromFileLocation($src) {
        $extension = strtolower(substr($src,strrpos($src,".")+1));
	if ($src == ".") {
		return "httpd/unix-directory";
	}
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
            case "configxml":
            case "children":
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
            case "zip":
            return "application/zip";
            case "gz":
            case "tgz":
            return "application/x-gzip";
            case "bz2":
            return "application/x-bz2";
            case "tar":
            return "application/x-gtar";
            case "torrent":
            return "application/x-bittorrent";
            case "mp3":
            return "audio/mpeg";
            
            default:
            
            if (strpos($src,"://") == false && file_exists($src)) {
                if (function_exists("finfo_open")) {
                    $res = finfo_open(FILEINFO_MIME);
                    $m = finfo_file($res, $src);
                    finfo_close($res);
                } else {
                    exec(escapeshellcmd('file -ib '. escapeshellarg($src)), $out);
                    $m = array_shift($out);
                }
                if ($m) {
                    return $m;
                }
            }
            return "application/octet-stream";
        }


    }

}
