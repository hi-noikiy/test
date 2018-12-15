<?php
class EM_LayeredNavigation_Model_Catalog_Layer extends Mage_Catalog_Model_Layer
{
	public function getFilterableAttributes() {
		/*
		$setIds = $this->_getSetIds();
		if (!$setIds) {
		return array();
		}
		*/

		$collection = Mage::getResourceModel('catalog/product_attribute_collection');
		$collection->setItemObjectClass('catalog/resource_eav_attribute')
			//->setAttributeSetFilter($setIds)
			->addStoreLabel(Mage::app()->getStore()->getId())->setOrder('position', 'ASC');
		$collection = $this->_prepareAttributeCollection($collection);
		$collection->load();
		
		return $collection;
	}
}
