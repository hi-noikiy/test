<?php

class Ktpl_Guestabandoned_Block_Adminhtml_View extends Mage_Adminhtml_Block_Template
{
	
	public function getHeaderText()
    {
    	$id = $this->getRequest()->getParam('entity_id');
        
        return Mage::helper('guestabandoned')->__('ID # %s | %s', $id,  $this->formatDate($this->getloadedquote()->getCreatedAt(), 'medium', true));
    }
	public function getloadedquote()
    {
    	$id = $this->getRequest()->getParam('entity_id');
    	$quote = Mage::getModel('sales/quote')->getCollection();
    	$quote->addFieldToFilter('entity_id',$id);

        return $quote->getFirstItem();
    }
}