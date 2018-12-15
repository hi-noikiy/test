<?php
class EM_Link_Block_Link extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getLink()     
     { 
        if (!$this->hasData('link')) {
            $this->setData('link', Mage::registry('link'));
        }
        return $this->getData('link');
        
    }
	public function getFormActionUrl()
    {
        return $this->getUrl('link/index/post', array('_secure' => true));
    }
	public function getSuccessMessage()
    {
        $message = Mage::getSingleton('newsletter/session')->getSuccess();
        return $message;
    }

    public function getErrorMessage()
    {
        $message = Mage::getSingleton('newsletter/session')->getError();
        return $message;
    }
}