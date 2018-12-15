<?php
class  Ktpl_Guestabandoned_Helper_Data extends Mage_Core_Helper_Abstract
{	
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('customer/guestabandoned/enabled',Mage::app()->getStore()->getId());
    }
}