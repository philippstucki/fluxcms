<?php

    require_once "HTTP/WebDAV/Server/Filesystem.php";
    
    /**
     * Filesystem access using WebDAV
     *
     * @access public
     */
    class HTTP_WebDAV_Server_bxcmsng extends HTTP_WebDAV_Server_Filesystem
    {
        /**
         * PROPFIND method handler
         *
         * @param  array  general parameter passing array
         * @param  array  return array for file properties
         * @return bool   true on success
         */
        function PROPFIND(&$options, &$files) 
        {
            // get absolute fs path to requested resource
            $fspath = $this->base . $options["path"];
            $coll = bx_collections::getCollection($options["path"]);
            // sanity check
            if (!$coll) {
                return false;
            }
            
            
            // prepare property array
            $files["files"] = array();

            // store information for the requested path itself
            $files["files"][] = $this->fileinfo("", $coll);

     
            // information for contained resources requested?
            if (!empty($options["depth"]))  { // TODO check for is_dir() first?
                
                // make sure path ends with '/'
                if (substr($options["path"],-1) != "/") { 
                    $options["path"] .= "/";
                }

                foreach ($coll->getOutputChildren() as $child) {
                    //error_log($);
                        $files["files"][] = $this->fileinfo($options["path"],$child);
                }
            }

            // ok, all done
            return true;
        } 
        
        function fileinfo($path, $res) 
        {
            // map URI path to filesystem path
            $fspath = $this->base . $path.$res->name;
            $path = $path.$res->rawname;
               // create result array
            $info = array();
            $info["path"]  = $path;    
            $info["props"] = array();
            
            // no special beautified displayname here ...
            $info["props"][] = $this->mkprop("displayname", strtoupper($path));
            
            
            // creation and modification time
            $info["props"][] = $this->mkprop("creationdate",    $res->ctime);
            $info["props"][] = $this->mkprop("getlastmodified", $res->mtime);

            // type and size (caller already made sure that path exists)
            if ($res->mimetype == "httpd/unix-directory") {
                // directory (WebDAV collection)
                $info["props"][] = $this->mkprop("resourcetype", "collection");
                $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");             
                $info["props"][] = $this->mkprop("getcontentlength", 0);
            } else {
                // plain file (WebDAV resource)
                $info["props"][] = $this->mkprop("resourcetype", "");
                $info["props"][] = $this->mkprop("getcontenttype", $res->mimetype);
                $info["props"][] = $this->mkprop("getcontentlength", $res->size); //filesize($fspath));
            }

            // get additional properties from database
            $query = "SELECT ns, name, value FROM properties WHERE path = '$path'";
            $res = mysql_query($query);
            while($row = mysql_fetch_assoc($res)) {
                $info["props"][] = $this->mkprop($row["ns"], $row["name"], $row["value"]);
            }
            mysql_free_result($res);

            return $info;
        }
      
        /**
         * PUT method handler
         * 
         * @param  array  parameter passing array
         * @return bool   true on success
         */
        function PUT(&$options) 
        {
            include_once("popoon/streams/bx.php");
            stream_wrapper_register("bx", "bxStream");

            $fspath =  $options["path"];
/*
            if(!@is_dir(dirname($fspath))) {
                return "409 Conflict";
            }
*/
            
            $streamtype = $this->getStreamType($options["path"]);
            
            
            if ($streamtype) {
                $fspath = "$streamtype:/".$fspath;
            }

            //$options["new"] = ! file_exists($fspath);
            $options["new"] = false;
            $fp = fopen($fspath, "w");
            
            return $fp;
        }
        
        function getStreamType($path) {
          
          $extension = substr(trim($path),strrpos(trim($path),".")+1);
          switch($extension) {
              case "html":
                include_once("popoon/streams/tidy.php");
                stream_wrapper_register("tidy", "TidyStream");
                return "tidy";
                break;
              case "sxw":
                include_once("popoon/streams/ooo.php");
                stream_wrapper_register("ooo", "OooStream");
                return "ooo";
              case "wiki":
                include_once("popoon/streams/wiki.php");
                stream_wrapper_register("wiki", "WikiStream");
                return "wiki";  
              default:
                return null;
          }
              
        }
        
        
    }
    
    ?>