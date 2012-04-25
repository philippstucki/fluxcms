<?php
// +----------------------------------------------------------------------+
// | Flux CMS                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Philipp Stucki <philipp.stucki@gmail.com>                    |
// +----------------------------------------------------------------------+
//
// $Id: crop.php 8653 2007-05-11 13:54:05Z chregu $

/**
 * megacrop implements an intelligent crop filter. Mcrop can handle all possible
 * combination of input and output proportions and crops without stretching.
 * 
 * Set 'center' to '1' to center the cropped area.  
 * Set 'clipping' to '1' if a proportional detail should be cropped.
 * Use 'background-color' to set the image background color.  
 *
 * @package bx_dynimage
 * @category 
 * @author Philipp Stucki <philipp.stucki@gmail.com>
 */
class bx_dynimage_filters_gd_megacrop extends bx_dynimage_filters_gd {

    protected function isLandscape($size) {
        return $size['w'] >= $size['h'] ? TRUE : FALSE;
    }
    
    protected function isPortrait($size) {
        return $size['h'] >= $size['w'] ? TRUE : FALSE;
    }
    
    protected function isCenterSet() {
        return isset($this->parameters['center']) && $this->parameters['center'] == '1' ? TRUE : FALSE;
    }
    
    protected function isClippingSet() {
        return isset($this->parameters['clipping']) && $this->parameters['clipping'] == '1' ? TRUE : FALSE;
    }
    
    public function start($imgIn) {
        
        $imgOut = imagecreatetruecolor($this->imageEndSize['w'], $this->imageEndSize['h']);

        
        // create image background if needed
        if(!$this->isClippingSet() && !empty($this->parameters['background-color'])) {
            $bgColorH = hexdec($this->parameters['background-color']);
    
            $bgColor = imagecolorallocate(
                $imgOut,
                0xFF & ($bgColorH >> 0x10),
                0xFF & ($bgColorH >> 0x8),
                0xFF & $bgColorH
            );
            
            imagefilledrectangle($imgOut, 0, 0, $this->imageEndSize['w'], $this->imageEndSize['h'], $bgColor);
        }
        
        $srcWidth = $this->imageOriginalSize['w'];
        $srcHeight = $this->imageOriginalSize['h'];
        
        $endWidth = $this->imageEndSize['w'];
        $endHeight = $this->imageEndSize['h'];
        
        $srcX = 0;
        $srcY = 0;
        
        $endX = 0;
        $endY = 0;
        
        $oProp = $this->imageOriginalSize['w']/$this->imageOriginalSize['h'];
        $eProp = $this->imageEndSize['w']/$this->imageEndSize['h'];
        
        // p => ls (or p => p or ls => ls)
        if(
            ($oProp<1 && $eProp > 1) 
            || ($eProp > $oProp)
        ) {
            if($this->isClippingSet()) {
                $srcHeight = $this->imageOriginalSize['w']*$this->imageEndSize['h']/$this->imageEndSize['w'];
            } else {
                $endWidth = $this->imageOriginalSize['w']*$this->imageEndSize['h']/$this->imageOriginalSize['h'];
            }

        // ls => p (or p => p or ls => ls)
        } else {
            
            if($this->isClippingSet()) {
                $srcWidth = $this->imageOriginalSize['h']*$this->imageEndSize['w']/$this->imageEndSize['h'];  
            } else {
                $endHeight = $this->imageOriginalSize['h']/$this->imageOriginalSize['w']*$this->imageEndSize['w'];
            }
            
        }

        // center cropped area if needed
        if($this->isCenterSet()) {
            $srcX = ($this->imageOriginalSize['w'] - $srcWidth)/2;
            $srcY = ($this->imageOriginalSize['h'] - $srcHeight)/2;
            if(!$this->isClippingSet()) {
                $endX = ($this->imageEndSize['w'] - $endWidth)/2;
                $endY = ($this->imageEndSize['h'] - $endHeight)/2;
            }
        }
        
        // crop / resize image
        imagecopyresampled(
            $imgOut, 
            $imgIn, 
            $endX, $endY,
            $srcX, $srcY, 
            $endWidth, 
            $endHeight, 
            $srcWidth,  
            $srcHeight
        );
        imagedestroy($imgIn);
        
        return $imgOut;
    }
    
    public function getEndSize($imgSize) {
        $endSize = $imgSize;
        
        if(!empty($this->parameters['w']) && !empty($this->parameters['h'])) {
            $endSize['w'] = (int) $this->parameters['w'];
            $endSize['h'] = (int) $this->parameters['h'];
        }
        
        return $endSize;
    }
    
}
