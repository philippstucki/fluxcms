<?php
    

class popoon_classes_browser {
        
    static private $BrowserName = "Unknown";
    static private $BrowserSubName = "None";
    static private $Version = "Unknown";
    static private $Platform = "Unknown";
    static private $UserAgent = "Not reported";
    static private $isMobile = null;
    
    static private $initialized = false;
    static private $parsed = false;
    
    private function __construct() {}
    
    static function init() {
        if (!self::$initialized) {
            if (isset( $_SERVER['HTTP_USER_AGENT'])) {
                self::$UserAgent = $_SERVER['HTTP_USER_AGENT'];
            }
            self::$initialized = true;
        }
    }
    
    
    static function isMozilla() {
        return( self::getName() == "mozilla");
    }
    
    static function isMozillaAndHasMidas() {
        if (self::getName() == "mozilla") {
            if (stripos(self::$UserAgent,"camino/0.8.")) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
    
    static function hasContentEditable() {
        return (self::isMozillaAndHasMidas() || self::isMSIEWin());
           
    }
    
    
    
    
    static function hasBadCss() {
           self::init();
           $name = self::getName();
           $version = self::getVersion();
           if ($name == "opera" && $version < 8) {
               return true;
           }
           if ($name == "mozilla" && $version < 5) {
               return true;
           }
           return false;
           
    }
    static function isPalm() {
        return (self::getPlatform()=="palm");
    }
    
    static function isMobile() {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)
            ||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
            return true;
        } else {
            return false;
        }
    }
    
    static function supportedByFCK() {
        
        return (self::isMozilla() || self::isMSIEWin() || self::isSafari3() || self::isOpera95());
    }

    static function isMSIEWin() {
        return( self::getName() == "msie" && self::getPlatform()=="windows");
    }
    
    static function isSafari() {
        return( self::getName() == "safari" );
    }
    
    static function isSafari3() {
        return( self::getName() == "safari" && self::getVersion() >= 3);
    }
    
    static function isOpera8() {
        return( self::getName() == "opera"  && self::getVersion() >= 8);
    }
     static function isOpera95() {
        return( self::getName() == "opera"  && self::getVersion() >= 9.5);
    }
    
    
    static function isKonqueror34() {
        return( self::getName() == "konqueror"  && self::getVersion() >= 3.4);
    }
    
    static function getName() {
        self::parse();
        return self::$BrowserName;
    }
    static function getSubName() {
        self::parse();
        return self::$BrowserSubName;
    }
    
    static function getVersion() {
        self::parse();
        return self::$Version;
    }
    
    static function getPlatform() {
        self::parse();
        return self::$Platform;
    }
    
    static function getAgent() {
        self::init();
        return self::$UserAgent;
    }
    
    static function parse(){
        
        if (!self::$parsed) {
            self::init();
            $agent = self::$UserAgent;
            // initialize properties
            $bd['platform'] = "Unknown";
            $bd['browser'] = "Unknown";
            $bd['version'] = "Unknown";

            // find operating system
            $OSs = array(
                'win' => 'windows',
                'mac' => 'macintosh',
                'linux' => 'linux',
                'os/2' => 'os/2',
                'beos' => 'beos',
                'palm' => 'palm',
            );
            foreach ($OSs as $OS => $OSname) {
                if (stripos($agent, $OS) !== false) {
                    $bd['platform'] = $OSname;
                    break;
                }
            }

            // test for Opera
            if ($val = stristr($agent, "opera")) {
                if (stripos($val, '/') !== false) {
                    $val = explode("/",$val);
                    $bd['browser'] = $val[0];
                    $val = explode(" ",$val[1]);
                    $bd['version'] = $val[0];
                }else{
                    $val = explode(" ",stristr($val,"opera"));
                    $bd['browser'] = $val[0];
                    $bd['version'] = $val[1];
                }

                // test for WebTV
            }elseif(stripos($agent, 'msie') !== false){
                $val = explode(" ",stristr($agent,"msie"));
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];

            }elseif(stripos($agent, 'galeon') !== false){
                $val = explode(" ",stristr($agent,"galeon"));
                $val = explode("/",$val[0]);
                $bd['browser'] = "Mozilla";
                $bd['version'] = $val[1];
                $bd['subbrowser']=$val[0];

                // test for Konqueror
            }elseif(stripos($agent, 'konqueror') !== false){
                $val = explode(" ",stristr($agent,"Konqueror"));
                $val = explode("/",$val[0]);
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];

            }elseif(stripos($agent, 'firebird') !== false){
                $bd['browser']="Mozilla";
                $bd['subbrowser']="Firefox";
                $val = stristr($agent, "Firebird");
                $val = explode("/",$val);
                $bd['version'] = $val[1];

                // test for Firefox
            }elseif(stripos($agent, 'firefox') !== false){
                $bd['browser']="Mozilla";
                $bd['subbrowser'] = "Firefox";
                $val = stristr($agent, "Firefox");

                $val = explode("/",$val);
                $bd['version'] = $val[1];
            }elseif(stripos($agent, 'mozilla') !== false && preg_match('/rv:[0-9]\.[0-9]/i', $agent) && stripos($agent, 'netscape' === false)){
                $bd['browser'] = "Mozilla";
                $bd['subbrowser'] = "Mozilla";
                $val = explode(" ",stristr($agent,"rv:"));
                preg_match('/rv:[0-9]\.[0-9]\.[0-9]/i',$agent, $val);
                $bd['version'] = str_replace("rv:","",$val[0]);

            }elseif(stripos($agent, 'safari') !== false){
                $bd['browser'] = "Safari";
                $val = substr($agent,strpos($agent,"Safari/") + 7);
                $bd['version'] = $val;

                // remaining two tests are for Netscape
            }elseif(stripos($agent, 'netscape') !== false){
                $val = explode(" ",stristr($agent,"netscape"));
                $val = explode("/",$val[0]);
                $bd['browser'] = $val[0];
                $bd['version'] = $val[1];

            }elseif(stripos($agent, 'mozilla') !== false && !preg_match('/rv:[0-9]\.[0-9]\.[0-9]/i', $agent)){
                $val = explode(" ",stristr($agent,"mozilla"));
                $val = explode("/",$val[0]);
                $bd['browser'] = "Mozilla";
                $bd['subbrowser'] = "Netscape";
                if (isset($val[1])) {
                    $bd['version'] = $val[1];
                }
            }

            // clean up extraneous garbage that may be in the name
            $bd['browser'] = preg_replace('/[^a-zA-Z]/', '', $bd['browser']);
            // clean up extraneous garbage that may be in the version
            $bd['version'] = preg_replace('/[^0-9.a-zA-Z]/', '', $bd['version']);

            // finally assign our properties
            self::$BrowserName = strtolower($bd['browser']);
            if (isset($bd['subbrowser'])) {
                self::$BrowserSubName = strtolower($bd['subbrowser']);
            }
            self::$Version = $bd['version'];
            self::$Platform = $bd['platform'];
            self::$parsed = true;
        }
    }
}
