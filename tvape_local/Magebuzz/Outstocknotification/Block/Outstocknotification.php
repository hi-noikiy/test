<?php
/*
 * @Copyright (c) 2015 www.magebuzz.com
 */
class Magebuzz_Outstocknotification_Block_Outstocknotification extends Mage_Core_Block_Template {
  public function __construct() {
		parent::__construct();
		$this->setTemplate('outstocknotification/viewsotck.phtml');
		$session = Mage::getModel('customer/session');
		
		if ($session->isLoggedIn()) {
			$customer = $session->getCustomer()->getData(); 
			$customer_id = $customer['entity_id'];
		}
		$collection = Mage::getModel('productalert/stock')->getCollection()
			->addFieldToFilter('customer_id',$customer_id);
		$collection->getSelect()->distinct();        
		$this->setNotification($collection->getData());
	}

  public function getPagerHtml() {
		return $this->getChildHtml('pager');
	}

	public function getBackUrl() {
		return $this->getUrl('customer/account/');
	}
	
	public function getOutstocknotification() { 
		if (!$this->hasData('outstocknotification')) {
			$this->setData('outstocknotification', Mage::registry('outstocknotification'));
		}
		return $this->getData('outstocknotification');		
	}
}