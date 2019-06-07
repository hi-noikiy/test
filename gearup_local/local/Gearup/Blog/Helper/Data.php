<?php

class Gearup_Blog_Helper_Data {

    public function getPostRating() {
        
    }

    public function getResizedImage($item, $width=null, $height=null)
    {
        // If base image exists
        if (is_file($this->getMediaPath($item))) {
            // If no resize return base image url
            if ($width==null && $height==null) {
                return $this->getMediaUrl($item);
            }
            // If resized image doesn't exists : process resize
            elseif (!is_file($this->getMediaPath($item, $width, $height))) {
                $imageObj = new Varien_Image($this->getMediaPath($item));
                $imageObj->constrainOnly(false);
                $imageObj->backgroundColor(array(252, 252, 252));
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(true);
                $imageObj->resize($width, $height);
                $imageObj->save($this->getMediaPath($item, $width, $height));
                // If resized image exists : return resized url
                if (is_file($this->getMediaPath($item, $width, $height))) {
                    return $this->getMediaUrl($item, $width, $height);
                }
            }
            // Resized image exists : return it
            else {
                return $this->getMediaUrl($item, $width, $height);
            }
        }
        return '';
    }

    public function getMediaPath($item, $width=null, $height=null)
    {
        $baseName = basename($item->getFeaturedImage());
        if ($width==null && $height==null) {
            return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'awblogpic' . DS . $baseName;
        }
        return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'awblogpic' . DS . 'resized' . DS . $width.'x'.$height . DS . $baseName;
    }

    public function getMediaUrl($item, $width=null, $height=null)
    {
        $baseName = basename($item->getFeaturedImage());
        if ($width==null && $height==null) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'awblogpic/' . $baseName;
        }
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'awblogpic/resized/' . $width.'x'.$height . '/' . $baseName;
    }
}
