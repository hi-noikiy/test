<?php
class EM_SendSMS_Block_SendSMS extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getSendSMS()     
     { 
        if (!$this->hasData('sendsms')) {
            $this->setData('sendsms', Mage::registry('sendsms'));
        }
        return $this->getData('sendsms');
        
    }
}