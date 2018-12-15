<?php
class EM_LayeredNavigation_Model_Search_Filter_Attribute extends Mage_CatalogSearch_Model_Layer_Filter_Attribute {
	protected $_appliedValues = array();
	
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
		$filter = $request->getParam($this->_requestVar);
		if (is_array($filter)) {
			return $this;
		}
		
		$text = $this->_getOptionText($filter);
		//if ($filter && strlen($text)) {		// comment this to allow apply multi value for filter
		if ($filter) {
			$this->_appliedValues = explode('_', $filter);
			//var_dump($this->_appliedValues); die;
			$this->_getResource()->applyFilterToCollection($this, $filter);
			$this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
			//$this->_items = array();	// prevent filter from hiding when apply to collection
		}
		return $this;
	}
	
	protected function _getItemsData() {
		$attribute         = $this->getAttributeModel();
		$this->_requestVar = $attribute->getAttributeCode();
		
		$key  = $this->getLayer()->getStateKey() . '_' . $this->_requestVar;
		$data = $this->getLayer()->getAggregator()->getCacheData($key);
		
		if ($data === null) {
			$options      = $attribute->getFrontend()->getSelectOptions();
			$optionsCount = $this->_getResource()->getCount($this);
			$data         = array();
			foreach ($options as $option) {
				if (is_array($option['value'])) {
					continue;
				}
				if (Mage::helper('core/string')->strlen($option['value'])) {
					// Check filter type
					if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
						// keep applied options in filter
						if (!empty($optionsCount[$option['value']]) || in_array($option['value'], $this->_appliedValues)) {
							$data[] = array(
								'label' => $option['label'],
								'value' => $option['value'],
								'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0		// product count fix
							);
						}
					} else {
						$data[] = array(
							'label' => $option['label'],
							'value' => $option['value'],
							'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0
						);
					}
				}
			}
			
			$tags = array(
				Mage_Eav_Model_Entity_Attribute::CACHE_TAG . ':' . $attribute->getId()
			);
			
			$tags = $this->getLayer()->getStateTags($tags);
			$this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
		}
		return $data;
	}
}
