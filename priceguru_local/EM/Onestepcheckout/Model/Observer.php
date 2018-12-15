<?php
class EM_Onestepcheckout_Model_Observer
{
	public function addLayout($observer) {
		$isEnable = Mage::getStoreConfig('onestepcheckout/general/enable');
		if (Mage::app()->getRequest()->getControllerName()=='onepage' && $isEnable) {
			$update = $observer->getEvent()->getLayout()->getUpdate();
			$update->addHandle('CHECKOUT_ONESTEP_INDEX');
		}
	}
}