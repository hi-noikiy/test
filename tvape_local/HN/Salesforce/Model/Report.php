<?php
/**
 * 
 */
class HN_Salesforce_Model_Report extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'salesforce/report' );
	}

	public function saveReport($id, $action, $table)
	{
		$datetime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
		$admin_user = Mage::getSingleton('admin/session')->getUser();
		$current_user = Mage::getSingleton('customer/session')->getCustomer();
		if ($admin_user) {
			$name = $admin_user->getName();
			$email = $admin_user->getEmail();
		}elseif($current_user->getName()) {
			$name = $current_user->getName();
			$email = $current_user->getEmail();
		}else{
			$name = "Guest";
			$email = '';
		}
		$data = [
			'record_id' => $id,
			'action' => $action,
			'table' => $table,
			'datetime' => $datetime,
			'username' => $name,
			'email' => $email,
			'status' => 1
		];
		$this->setData($data);
		$this->save();
		return;
	}
}