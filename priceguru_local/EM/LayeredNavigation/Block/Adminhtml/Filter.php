<?php
class EM_LayeredNavigation_Block_Adminhtml_Filter extends Mage_Adminhtml_Block_Widget_Grid_Container {
	public function __construct() {
		parent::__construct();
		$this->_controller = 'adminhtml_filter';
		$this->_blockGroup = 'layerednavigation';
		$this->_headerText = Mage::helper('layerednavigation')->__('Layered Navigation Filters');
	}

    protected function _prepareLayout() {
		$this->removeButton('add');
		return parent::_prepareLayout();
    }

}