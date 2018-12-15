<?php
class HN_Salesforce_Block_Adminhtml_Report extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_report';
		$this->_blockGroup = 'salesforce';
		$this->_headerText = Mage::helper('salesforce')->__('Show Report');
		parent::__construct();
		$this->_removeButton('add');
	}
}
