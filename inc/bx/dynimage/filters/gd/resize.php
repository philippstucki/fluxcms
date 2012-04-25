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
// | Author: Liip AG      <contact@liip.ch>                               |
// +----------------------------------------------------------------------+
//
// $Id: resize.php 8653 2007-05-11 13:54:05Z chregu $

/**
 * DOCUMENT_ME
 *
 * @package bx_dynimage
 * @category 
 * @author Liip AG      <contact@liip.ch>
 */
class bx_dynimage_filters_gd_resize extends bx_dynimage_filters_gd {
    
    protected function getProportions($size) {
        return $size['w']/$size['h'];
    }

    protected function isAutorotateSet() {
        return isset($this->parameters['autorotate']) && $this->parameters['autorotate'] == '1' ? TRUE : FALSE;
    }

    protected function needsAutorotate($size) {
        return $this->isAutorotateSet() && $this->getProportions($size) < $this->parameters['autorotateTreshold'];
    }

    public function start($imgIn) {
        $imgOut = imagecreatetruecolor($this->imageEndSize['w'], $this->imageEndSize['h']);
        if ($this->needsAutorotate($this->imageOriginalSize)) {
            $imgIn = imagerotate($imgIn, 90, 0);
            $w = $this->imageOriginalSize['w'];
            $this->imageOriginalSize['w'] = $this->imageOriginalSize['h'];
            $this->imageOriginalSize['h'] = $w;
        }
        imagecopyresampled($imgOut, $imgIn, 0, 0, 0, 0, $this->imageEndSize['w'], $this->imageEndSize['h'], $this->imageOriginalSize['w'], $this->imageOriginalSize['h']);
        imagedestroy($imgIn);
        return $imgOut;
    }
    
    public function getEndSize($imgSize) {
        
        if ($this->needsAutorotate($imgSize)) {
            $w = $imgSize['w'];
            $h = $imgSize['h'];
            $imgSize['w'] = $h;
            $imgSize['h'] = $w;
        }

        $endSize = array();
        $endSize['w'] = $imgSize['w'];
        $endSize['h'] = $imgSize['h'];
        
        // 'w' has precedence over 'h', so w:nn,h:mm will create a picture of nn pixels width 
        if(!empty($this->parameters['w'])) {
            if($this->parameters['w'] < $imgSize['w']) {
                $endSize['w'] = (int) $this->parameters['w'];
                $endSize['h'] = (int) round($this->parameters['w'] * $imgSize['h'] / $imgSize['w']);
            }
        }
        if(!empty($this->parameters['h']) && empty($this->parameters['w'])) {
            if($this->parameters['h'] < $imgSize['h']) {
                $endSize['h'] = (int) $this->parameters['h'];
                $endSize['w'] = (int) round($imgSize['w'] / $imgSize['h'] * $this->parameters['h']);
            }
        }
        
        return $endSize;
    }
    
}
