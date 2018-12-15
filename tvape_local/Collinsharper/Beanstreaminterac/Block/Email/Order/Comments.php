<?php

 class Collinsharper_Beanstreaminterac_Block_Email_Order_Comments extends Mage_Sales_Block_Items_Abstract
{

	public function isActive() {
		if(Mage::getStoreConfig('payment/Beanstreaminterac/active') && ('Beanstreaminterac' == (string)$this->getOrder()->getPayment()->getMethod() ))
			return true;
		return false;
	}
	
	
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
	
	public function getBeanstreaminteracInfo() {
	if(!$this->isActive())
		return false;
	return $this->getReciept();
	
	}

	
		public function getReciept() {
		// $session = $this->getSession();
		// $order = Mage::getModel('sales/order');
        // $order->loadByIncrementId($session->getTransId());
		$order = $this->getOrder();
		if(!is_object($order))
			$order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastRealOrderId());
			
		foreach($order->getStatusHistoryCollection(true) as $_c) {
			$_comm = strip_tags($_c->getComment());
				mage::log("connent ". $_comm."\n");
			if(strpos($_comm , "Reference Number:")) {
				return $_comm;
				break;
			}
		}
		return false;
	}
	
}



