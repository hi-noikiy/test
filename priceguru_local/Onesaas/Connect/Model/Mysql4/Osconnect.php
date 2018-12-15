<?php
class Onesaas_Connect_Model_Mysql4_Osconnect extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {
		$this->_init('osconnect/osconnect', 'key_id');
	}
}
