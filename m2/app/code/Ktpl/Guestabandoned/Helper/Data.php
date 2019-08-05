<?php
namespace Ktpl\Guestabandoned\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{	
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('customer/guestabandoned/enabled',Mage::app()->getStore()->getId());
    }
}