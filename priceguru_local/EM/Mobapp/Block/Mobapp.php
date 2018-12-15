<?php
class EM_Mobapp_Block_Mobapp extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getMobapp()     
     { 
        if (!$this->hasData('mobapp')) {
            $this->setData('mobapp', Mage::registry('mobapp'));
        }
        return $this->getData('mobapp');
        
    }
}