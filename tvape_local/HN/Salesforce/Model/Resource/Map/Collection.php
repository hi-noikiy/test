<?php
class HN_Salesforce_Model_Resource_Map_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();

		$this->_init('salesforce/map');

	}
	
}
?>