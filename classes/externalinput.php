<?php

class popoon_classes_externalinput {
    
    // this basic clean should clean html code from
    // lot of possible malicious code for Cross Site Scripting
    // use it whereever you get external input    

    static function basicClean($string) {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        $string = str_replace(array("&amp;","&lt;","&gt;"),array("&amp;amp;","&amp;lt;","&amp;gt;",),$string);
        // fix &entitiy\n;
        $string = preg_replace('#(&\#*\w+)[\s\r\n]+;#U',"$1;",$string);
        $string = html_entity_decode($string, ENT_COMPAT, "UTF-8");
        
        
        // remove any attribute starting with "on" or xmlns
        $string = preg_replace('#(<[^>]+[\s\r\n\"\'])(on|xmlns)[^>]*>#iU',"$1>",$string);
        // remove javascript: and vbscript: protocol
        $string = preg_replace('#([a-z]*)[\s\r\n]*=[\s\n\r]*([\`\'\"]*)[\\s\n\r]*j[\s\n\r]*a[\s\n\r]*v[\s\n\r]*a[\s\n\r]*s[\s\n\r]*c[\s\n\r]*r[\s\n\r]*i[\s\n\r]*p[\s\n\r]*t[\s\n\r]*:#iU','$1=$2nojavascript...',$string);
        $string = preg_replace('#([a-z]*)[\s\r\n]*=([\'\"]*)[\s\n\r]*v[\s\n\r]*b[\s\n\r]*s[\s\n\r]*c[\s\n\r]*r[\s\n\r]*i[\s\n\r]*p[\s\n\r]*t[\s\n\r]*:#iU','$1=$2novbscript...',$string);
        //<span style="width: expression(alert('Ping!'));"></span> 
        // only works in ie...
        $string = preg_replace('#(<[^>]+)style[\s\r\n]*=[\s\r\n]*([\`\'\"]*).*expression[\s\r\n]*\([^>]*>#iU',"$1>",$string);
        $string = preg_replace('#(<[^>]+)style[\s\r\n]*=[\s\r\n]*([\`\'\"]*).*s[\s\n\r]*c[\s\n\r]*r[\s\n\r]*i[\s\n\r]*p[\s\n\r]*t[\s\n\r]*:*[^>]*>#iU',"$1>",$string);
        //remove namespaced elements (we do not need them...)
        $string = preg_replace('#</*\w+:\w[^>]*>#i',"",$string);
        //remove really unwanted tags
        
        do {
            $oldstring = $string;
            $string = preg_replace('#</*(style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i',"",$string);
        } while ($oldstring != $string);
        
        return $string;
    }
}   
