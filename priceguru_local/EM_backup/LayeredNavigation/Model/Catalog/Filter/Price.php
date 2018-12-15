<?php
class EM_LayeredNavigation_Model_Catalog_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    protected function _getItemsData()
    {
		// remove price condition in collection
		$select = $this->getLayer()->getProductCollection()->getSelect();
		$where = $select->getPart(Zend_Db_Select::WHERE);
		$select->reset(Zend_Db_Select::WHERE);

        $data = array();
        if (Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION) == self::RANGE_CALCULATION_IMPROVED) {
            $data = $this->_getCalculatedItemsData();
        } else {
			$range      = $this->getPriceRange();
			$dbRanges   = $this->getRangeItemCounts($range);
			if (!empty($dbRanges)) {
				$lastIndex = array_keys($dbRanges);
				$lastIndex = $lastIndex[count($lastIndex) - 1];

				foreach ($dbRanges as $index => $count) {
					$fromPrice = ($index == 1) ? '' : (($index - 1) * $range);
					$toPrice = ($index == $lastIndex) ? '' : ($index * $range);

					$data[] = array(
						'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
						'value' => $fromPrice . '-' . $toPrice,
						'count' => $count,
					);
				}
			}
		}
		
        // restore price condition
		$select->setPart(Zend_Db_Select::WHERE, $where);
        return $data;
    }
	
	protected function _getResource() {
		if (is_null($this->_resource)) {
			$this->_resource = Mage::getResourceModel('layerednavigation/catalog_filter_price');
		}
		return $this->_resource;
	}
}
