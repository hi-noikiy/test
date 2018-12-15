<?php

class Ktpl_Customreport_Block_Adminhtml_Purchaseorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_purchaseorder';
	    $this->_blockGroup = 'customreport';
	    $this->_headerText = Mage::helper('customreport')->__('Purchase Orders');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}