<?php

class EM_AdvertiseLeft_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('advertiseleft')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('advertiseleft')->__('Disabled')
        );
    }
}