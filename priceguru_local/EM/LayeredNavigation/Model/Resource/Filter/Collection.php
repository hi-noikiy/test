<?php
class EM_LayeredNavigation_Model_Resource_Filter_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    protected function _construct() {
        $this->_init('layerednavigation/filter');
    }
	
	public function getDisplayConfigs() {
		$this->addFieldToSelect('attribute_code');
		$this->addFieldToSelect('display_as');
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		return $connection->fetchPairs($this->getSelect());
	}
}