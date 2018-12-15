<?php

class Ktpl_Guestabandoned_Model_Status extends Varien_Object
{
    const STATUS_IN_PROGRESS	= 1;
    const STATUS_CAPTURED	= 2;
    const STATUS_CLOSED	= 3;
    
    static public function getOptionArray()
    {
        return array(
            self::STATUS_IN_PROGRESS    => Mage::helper('guestabandoned')->__('In Progress'),
            self::STATUS_CAPTURED   => Mage::helper('guestabandoned')->__('Captured'),
            self::STATUS_CLOSED   => Mage::helper('guestabandoned')->__('Close')                
        );
    }
}