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
        
        $string = html_entity_decode($string, ENT_COMPAT, "UTF-8");
        $string = preg_replace('#</*(script|embed|object|iframe)[^>]*>#i',"",$string);
        $string = preg_replace('#(<[^>]+)[\s\r\n]on[^>]*>#iU',"$1>",$string);
        $string = preg_replace('#([a-z]*)[\s\r\n]*=[\s\r\n]*([\'\"]*)[\s\r\n]*j[\s\r\n]*a[\s\r\n]*v[\s\r\n]*a[\s\r\n]*s[\s\r\n]*c[\s\r\n]*r[\s\r\n]*i[\s\r\n]*p[\s\r\n]*t[\s\r\n]*:#iU','$1=$2nojavascript...',$string);
       return $string;
    }
}