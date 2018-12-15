<?php
/**
 * 
 */
class HN_Salesforce_Model_Map extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'salesforce/map' );
	}

}