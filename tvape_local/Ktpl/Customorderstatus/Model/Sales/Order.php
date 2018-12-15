<?php
class Ktpl_Customorderstatus_Model_Sales_Order extends Mage_Sales_Model_Order
{
	
   	protected function _setState($state, $status = false, $comment = '',$isCustomerNotified = null, $shouldProtectState = false)
	{

		Mage::dispatchEvent('sales_order_status_before', array('order' => $this, 'state' => $state, 'status' => $status, 'comment' => $comment, 'isCustomerNotified' => $isCustomerNotified, 'shouldProtectState' => $shouldProtectState));
			
		if ($shouldProtectState) {
			if ($this->isStateProtected($state)) {
				Mage::throwException(
					Mage::helper('sales')->__('The Order State "%s" must not be set manually.', $state)
				);
			}
		}
		$this->setData('state', $state);

		// add status history
		if ($status) {
			if ($status === true) {
				$status = $this->getConfig()->getStateDefaultStatus($state);
			}
			$this->setStatus($status);
			$history = $this->addStatusHistoryComment($comment, false);
			$history->setIsCustomerNotified($isCustomerNotified);
		}

		Mage::dispatchEvent('sales_order_status_after', array('order' => $this, 'state' => $state, 'status' => $status, 'comment' => $comment, 'isCustomerNotified' => $isCustomerNotified, 'shouldProtectState' => $shouldProtectState));
		
		return $this;
	}
}