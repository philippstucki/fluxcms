<?php

class popoon_classes_externalinput {
    
    // this basic clean should clean html code from
    // lot of possible malicious code for Cross Site Scripting
    // use it whereever you get external input    

    static function basicClean($string) {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        $string = html_entity_decode($string);
        $string = preg_replace('#</*(script|embed|object)[^>]*>#i',"",$string);
        $string = preg_replace('#(<[^>]+)\son[^>]*>#iU',"$1>",$string);
        $string = preg_replace('#([a-z]*)\s*=\s*([\'\"]*)\s*j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:#i','$1=$2nojavascript...',$string);
        return $string;
    }
}