<?php

    require_once "HTTP/WebDAV/Server/Filesystem_MDB2.php";
    
    /**
     * Filesystem access using WebDAV
     *
     * @access public
     */
    class HTTP_WebDAV_Server_bxcmsng extends HTTP_WebDAV_Server_Filesystem_MDB2
    {
        
               /**
         * Serve a webdav request
         *
         * @access public
         * @param  string  
         */
        function ServeRequest($base = false) 
        {
            // special treatment for litmus compliance test
            // reply on its identifier header
            // not needed for the test itself but eases debugging
            foreach(apache_request_headers() as $key => $value) {
                if(stristr($key,"litmus")) {
                    error_log("Litmus test $value");
                    header("X-Litmus-reply: ".$value);
                }
            }

            // set root directory, defaults to webserver document root if not set
            if ($base) { 
                $this->base = realpath($base); // TODO throw if not a directory
            } else if(!$this->base) {
                $this->base = $_SERVER['DOCUMENT_ROOT'];
            }
            
            // establish connection to property/locking db
            
     

            
            require_once("MDB2.php");
            $this->db = MDB2::connect($GLOBALS['POOL']->config->dsn,$GLOBALS['POOL']->config->dboptions);
            
            if (MDB2::isError($this->db)) {
                die( $this->db->getMessage());
            }
           
            //mysql_select_db($this->db_name) or die(mysql_error());
            // TODO throw on connection problems

            // let the base class do all the work
            parent::ServeRequest();
        }
         
   
        
        
        
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
            $path = "";
            $coll = bx_collections::getCollection($options["path"]."/index.html");
            // sanity check
            if (!$coll) {
                $coll = bx_collections::getCollection($options["path"]);
                
                //FIXME: we should have a $coll->getResource($filename) method..
                $ch = $coll->getOutputChildren();
                $path = $coll->uri;
                $coll = $ch[str_replace($coll->uri,"",$options["path"])];
                if (!$coll) {
                    error_log("404 " .  $options["path"]);
                    return false;
                }
            }
            
            
            // prepare property array
            $files["files"] = array();

            // store information for the requested path itself
//
            $files["files"][] = $this->fileinfo($path, $coll);
     
            // information for contained resources requested?
             
            if (!empty($options["depth"]))  { // TODO check for is_dir() first?
                // make sure path ends with '/'
                if (substr($options["path"],-1) != "/") { 
                    $options["path"] .= "/";
                }

                foreach ($coll->getOutputChildren() as $child) {
                   
                        $files["files"][] = $this->fileinfo($options["path"],$child);
                }
            } 

            // ok, all done
            return true;
        }
        
        /**
         * PROPPATCH method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function proppatch(&$options) 
        {
            global $prefs, $tab;

            $msg = "";
            
            $path = $options["path"];
            
            $dir = dirname($path)."/";
            $base = basename($path);
            
            foreach($options["props"] as $key => $prop) {
                if($ns == "DAV:") {
                    $options["props"][$key][$status] = "403 Forbidden";
                } else {
                    bx_resources::setProperty($options['path'],$prop['name'],$prop['ns'], $prop['val']);
                }
            }
                        
            return "";
        }
        
           /**
         * MKCOL method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function MKCOL($options) 
        {           
            $path = $this->base .$options["path"];
            $parent = dirname($path);
            $name = basename($path);

            if(!file_exists($parent)) {
                return "409 Conflict";
            }

            if(!is_dir($parent)) {
                return "403 Forbidden";
            }

            if( file_exists($parent."/".$name) ) {
                return "405 Method not allowed";
            }

            if(!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
                return "415 Unsupported media type";
            }
            
            $stat = mkdir ($parent."/".$name,0777);
            if(!$stat) {
                return "403 Forbidden";                 
            }
            
            copy($parent."/.config.xml",$parent."/".$name."/.config.xml");

            return ("201 Created");
        }
        
        function fileinfo($path, $res) 
        {
            // map URI path to filesystem path
            $fspath = $this->base . $path.$res->name;
            $path = $path.$res->rawname;
            //$path = str_replace("//","/",$path);
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
               // $info["props"][] = $this->mkprop("getcontentlength", 0);
            } else {
                // plain file (WebDAV resource)
                $info["props"][] = $this->mkprop("resourcetype", "");
                $info["props"][] = $this->mkprop("getcontenttype", $res->mimetype);
                $info["props"][] = $this->mkprop("getcontentlength", $res->size); //filesize($fspath));
            }

            // get additional properties from database
            
            $query = "SELECT ns, name, value FROM properties WHERE path = '$path'";
            $res = $this->db->query($query);
            while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $info["props"][] = $this->mkprop($row["ns"], $row["name"], $row["value"]);
            }
            $res->free($res);

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
         /*   include_once("popoon/streams/bx.php");
            stream_wrapper_register("bx", "bxStream");
*/
            $fspath =  $options["path"];
/*
            if(!@is_dir(dirname($fspath))) {
                return "409 Conflict";
            }
*/
            
            //$streamtype = $this->getStreamType($options["path"]);
            
            
            
            $fspath = BX_DATA_DIR.$fspath;
            
            //$options["new"] = ! file_exists($fspath);
            $options["new"] = false;
            $fp = fopen($fspath, "w");
             bx_resources::setProperty($options["path"],"mime-type","bx","text/html");
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