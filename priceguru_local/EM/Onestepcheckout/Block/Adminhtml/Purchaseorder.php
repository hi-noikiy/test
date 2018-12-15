<?php

class EM_Onestepcheckout_Block_Adminhtml_Purchaseorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_purchaseorder';
	    $this->_blockGroup = 'onestepcheckout';
	    $this->_headerText = Mage::helper('onestepcheckout')->__('Purchase Orders');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}