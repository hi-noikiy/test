<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Helper_Image extends Mage_Core_Helper_Abstract
{
    public function resize($filename,$type,$width,$height=null)
    {
        //Zend_Debug::dump($filename);
        $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."configurator/".$filename;

        $width = (int) $width;
        if( $width ) {

            $path = Mage::getBaseDir('media').DS."configurator".DS;
            $file = $path.$filename;

            $pathParts = pathinfo($file);

            $newFilename = $pathParts['filename']."_".$width."_".$type.".".$pathParts['extension'];
            $newFile =  $pathParts['dirname'] .DS .$newFilename;

            $fileRelPath = explode('configurator/', $pathParts['dirname']);

            if(is_array($fileRelPath) && count($fileRelPath) > 1){
                $newFileSrc = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."configurator/" .$fileRelPath[1] .DS .$newFilename;
            }else{
                $newFileSrc = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."configurator/" .$newFilename;
            }

            if(file_exists($newFile)){
                return $newFileSrc;
            }

            $height = $height ? $height : null;

            try {
                $image = new Varien_Image($file);
                $image->keepTransparency(true);
                $image->keepAspectRatio(TRUE);
                $image->resize($width,$height);
                $image->save($newFile);
                $src = $newFileSrc;
            } catch (Exception $e) {
            }
        }

        return $src;
    }

    public function hexToRgb($hex) {
        $hex = preg_replace("/#/", "", $hex);
        $color = array();

        if(strlen($hex) == 3) {
            $color['r'] = hexdec(substr($hex, 0, 1));
            $color['g'] = hexdec(substr($hex, 1, 1));
            $color['b'] = hexdec(substr($hex, 2, 1));
        }
        else if(strlen($hex) == 6) {
            $color['r'] = hexdec(substr($hex, 0, 2));
            $color['g'] = hexdec(substr($hex, 2, 2));
            $color['b'] = hexdec(substr($hex, 4, 2));
        }

        return $color;
    }

    public function getFileExtension($filename) {
        $path_parts = pathinfo($filename);
        if (isset($path_parts['extension'])) {
            return $path_parts['extension'];
        }
        return false;
    }

    protected function getImageReadFunc($filetype) {
        switch ($filetype) {
            case "jpg":
            case "jpeg":
                return "imagecreatefromjpeg";
                break;
            case "png":
                return "imagecreatefrompng";
                break;
            case "gif":
                return "imagecreatefromgif";
                break;
        }

        return false;
    }

    protected function getImageWriteFunc($filetype) {
        switch ($filetype) {
            case "jpg":
            case "jpeg":
                return "imagejpeg";
                break;
            case "png":
                return "imagepng";
                break;
            case "gif":
                return "imagegif";
                break;
        }

        return false;
    }

    public function readImage($filename) {
        $createImageFunction = $this->getImageReadFunc($this->getFileExtension($filename));
        if($createImageFunction){
            $image =  $createImageFunction($filename);
            return $image;
        }
        return false;
    }

    public function writeImage($image, $filename) {
        $writeImageFunction = $this->getImageWriteFunc($this->getFileExtension($filename));
        if($writeImageFunction){
            $result =  $writeImageFunction($image, $filename);
            return $result;
        }
        return false;
    }
}