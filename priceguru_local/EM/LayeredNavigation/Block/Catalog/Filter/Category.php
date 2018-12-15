<?php
class EM_LayeredNavigation_Block_Catalog_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Category
{
	/**
	 * Change template & model of block
	 */
	public function __construct() {
		parent::__construct();
		$this->_filterModelName = 'layerednavigation/catalog_filter_category';
		$this->setTemplate('em_layerednavigation/filter/tree/view.phtml');
	}
	
	/**
	 * Prepare tree for category
	 *
	 * @return EM_LayeredNavigation_Block_Catalog_Filter_Category
	 */
	public function prepareTree() {
		$this->_prepareChilds($this, $this->getItems()); // create top level blocks
		return $this;
	}
	
	/**
	 * Init tree's items recursively
	 */
	protected function _prepareChilds($parent, $items) {
		foreach ($items as $key => $item) {
			// create & append block to parent
			$block = $this->getLayout()->createBlock('catalog/layer_filter_attribute', $parent->getNameInLayout() . '_' . $key, array(
				'item' => $item,
				'show_in_filter' => $this->getShowInFilter()
			));
			$block->setTemplate('em_layerednavigation/filter/tree/item.phtml');
			$parent->setChild($parent->getNameInLayout() . '_' . $key, $block);
			// create childs block recursively
			$this->_prepareChilds($block, $item->getItems());
		}
	}
}
