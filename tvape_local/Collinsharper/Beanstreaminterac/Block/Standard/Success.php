<?php

class Collinsharper_Beanstreaminterac_Block_Standard_Success extends Mage_Core_Block_Template
{
 
	public function getSession() {
		return Mage::getSingleton('customer/session');
	}
	
	protected function _construct()
    {
        $this->setTemplate('Beanstreaminterac/standard/success.phtml');
        parent::_construct();
    }

		public function getOrder() {
			$session = $this->getSession();
		  return Mage::getModel('sales/order')->loadByIncrementId($session->getOrderId());		
		}
		
	public function getReciept() {
		$session = $this->getSession();
		$order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getOrderId());
		foreach($order->getStatusHistoryCollection(true) as $_c) {
			$_comm = strip_tags($_c->getComment());

			if(strpos($_comm , "Reference Number:")) {
				return $_comm;
				break;
			}
		}
	}
	
}