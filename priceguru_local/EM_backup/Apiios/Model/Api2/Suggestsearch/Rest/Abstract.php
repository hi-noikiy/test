<?php
class EM_Apiios_Model_Api2_Suggestsearch_Rest_Abstract extends EM_Apiios_Model_Api2_Products
{
    protected function _retrieve(){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $result = array();

		$collection = Mage::helper('catalogsearch')->getSuggestCollection();
		$query = Mage::helper('catalogsearch')->getQueryText();
		$data = array();
		foreach ($collection as $item) {
			$_data = array(
				'name' => $item->getQueryText(),
				'num' => $item->getNumResults()
			);
			if ($item->getQueryText() == 'com') {
				array_unshift($data, $_data);
			}
			else {
				$data[] = $_data;
			}
		}
        $result['keywords']	=	$data;

    	return $result;
    }

}
?>