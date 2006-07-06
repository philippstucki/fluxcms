<?php
// +----------------------------------------------------------------------+
// | BxCMS                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <flux@bitflux.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * DOCUMENT_ME
 *
* @package bx_dbforms2
 * @category 
 * @author Bitflux GmbH <flux@bitflux.ch>
 */
class bx_dbforms2_common {

    /**
     *  Takes a DOMDocument and transforms it to XHTML using the given stylesheet.
     *
     *  @param  DOMDocument $dom The DOMDocument to be transformed.
     *  @param  string $tablePrefix The table prefix.
     *  @param  string $formxsl Absolute filename of the stylesheet to use.
     *  @access public
     *  @return DOMDocument The transformed result.
     */
    public static function transformFormXML($dom, $tablePrefix, $formxsl) {
        $xslt = new XSLTProcessor();
        $xsl = new DOMDocument();
            
        if ($formxsl && file_exists($formxsl)) {
            $xsl->load($formxsl);
        } else {
            throw new PopoonFileNotFoundException($formxsl);
        }
        
        $xslt->importStylesheet($xsl);
        $xslt->setParameter('', 'webroot', BX_WEBROOT);
        $xslt->setParameter('', 'tablePrefix', $tablePrefix);
        $xslt->registerPhpFunctions();
        
        $xml = $xslt->transformToDoc($dom);

        if (!$xml) {
            throw new PopoonXSLTParseErrorException($xslfile, $registerPhpFunctions);
        }
        
        return $xml;
        
    }
    
}

?>
