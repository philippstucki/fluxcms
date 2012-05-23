<?php

class bx_helpers_dom {

    
    public static function array2dom( $aData , $rootName = 'root' , $keyNames = Array() , $parentKey = NULL , $asDOM = TRUE ) {

        $xml = '';

        if( $asDOM === TRUE ) {
            $parentKey = $rootName;
        }

        foreach( $aData as $k => $v ) {

            if( $parentKey !== NULL  && isset( $keyNames[$parentKey] ) ) {
                $st = $keyNames[$parentKey];
            } else if( is_int( $k ) ) {
                $st = 'entry';
            } else {
                $st = $k;
            }

            $xml .= '<' . $st . ( is_int( $k ) === TRUE ? ' key="' . $k . '"'  : '' ) . '>';

            if( is_array( $v ) ) {
                $xml.= self::array2dom( $v , $rootName , $keyNames , $k , FALSE );
            } else {
                $xml.= (string) htmlspecialchars( $v , ENT_NOQUOTES );
            }

            $xml .= '</'.$st.'>';

        }

        if( $asDOM === FALSE ) {
            return $xml;
        } else {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . "<{$rootName}>" . $xml . "</{$rootName}>";
            $dom = new DOMDocument();
            $dom->loadXML( $xml );
            return $dom;
        }

    }

}
