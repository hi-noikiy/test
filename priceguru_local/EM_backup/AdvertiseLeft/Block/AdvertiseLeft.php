<?php
class EM_AdvertiseLeft_Block_AdvertiseLeft extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getAdvertiseLeft()     
     { 
        if (!$this->hasData('advertiseleft')) {
            $this->setData('advertiseleft', Mage::registry('advertiseleft'));
        }
        return $this->getData('advertiseleft');
        
    }
}