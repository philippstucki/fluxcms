<?php


class popoon_components_transformers_i18n_xml {
    
    function __construct($src,$lang) {
        $cat = domdocument::load($src.'_'.$lang.'.xml');
        $this->catctx = new DomXpath($cat);
        
    }
    
    
    function getText($key) {
        $catres = $this->catctx->query('/catalogue/message[@key = "'.$key.'"]');
        if($catres && $catres->length > 0) {
            return $catres->item(0)->nodeValue;
        }
        return false;
    }


}