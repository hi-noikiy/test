<?php
class HN_Salesforce_Block_Adminhtml_Map extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_map';
		$this->_blockGroup = 'salesforce';
		$this->_headerText = Mage::helper('salesforce')->__('Fields mapping management');
		$this->_addButtonLabel = Mage::helper('salesforce')->__('Add');

		parent::__construct();
	}
}
