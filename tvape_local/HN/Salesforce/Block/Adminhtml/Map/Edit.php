<?php
class HN_Salesforce_Block_Adminhtml_Map_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'map';
		$this->_blockGroup = 'salesforce';
		$this->_controller = 'adminhtml_map';
		$this->_updateButton('save', 'label', Mage::helper('salesforce')->__('Save'));
		$this->_updateButton('delete', 'label', Mage::helper('salesforce')->__('Delete'));
		
	}
	public function getHeaderText()
	{
		if($this->getRequest()->getParam('id')) 
			return Mage::helper('salesforce')->__("Edit Mapping");
		else 
			return Mage::helper('salesforce')->__('Add New Mapping');
	}
}
