<?php

class Redstage_SaveForLater_Model_Item extends Mage_Core_Model_Abstract {

	public function _construct(){
		parent::_construct();
		$this->_init('saveforlater/item');
	}

}