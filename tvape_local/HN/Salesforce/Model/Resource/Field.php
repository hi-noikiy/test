<?php
class HN_Salesforce_Model_Resource_Field extends Mage_Core_Model_Resource_Db_Abstract
{
	public function _construct()
	{
		$this->_init('salesforce/field', 'id');
	}
}