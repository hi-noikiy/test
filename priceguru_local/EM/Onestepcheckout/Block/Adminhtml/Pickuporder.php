<?php

class EM_Onestepcheckout_Block_Adminhtml_Pickuporder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_pickuporder';
	    $this->_blockGroup = 'onestepcheckout';
	    $this->_headerText = Mage::helper('onestepcheckout')->__('Pickup Orders');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}