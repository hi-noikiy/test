<?php

class Justselling_Configurator_Block_Adminhtml_Customer_Edit_Tabs extends Justselling_Configurator_Block_Adminhtml_Customer_Edit_Tabs_Amasty_Pure
{
    protected function _beforeToHtml()
    {
        $id = Mage::registry('current_customer')->getId();
        if ($id) {
            if ('true' == (string)Mage::getConfig()->getNode('modules/List_Email/active')){
                $this->addTab('email', array(
                    'label'     => Mage::helper('email')->__('Emails History'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('email/adminhtml_index/customer', array('customer_id' => $id)),
                    'after'     => 'tags',
                ));
            }
            
            $this->addTab('list', array(
                'label'     => Mage::helper('configurator')->__('Favorites'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('configurator/adminhtml_index/index', array('customer_id' => $id)),
                'after'     => 'wishlist',
            ));
        }
        
        $this->_updateActiveTab();
        return parent::_beforeToHtml();
    }
    
    protected function _updateActiveTab()
    {
    	$tabId = $this->getRequest()->getParam('tab');
    	if( $tabId ) {
    		$tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
    		if($tabId) {
    			$this->setActiveTab($tabId);
    		}
    	}
    } 
}