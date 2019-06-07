<?php

if (!@class_exists('Mobile_Detect')) {
    include_once 'Mobile_Detect.php';
}

class TM_ProLabels_Helper_Device extends Mage_Core_Helper_Abstract
{
    protected $mobileDetect = NULL;

    public function getMobileDetect()
    {
        if ($this->mobileDetect === NULL) {
            $this->mobileDetect = new Mobile_Detect;
        }
        return $this->mobileDetect;
    }

}
