<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_pickuporder';
	    $this->_blockGroup = 'customreport';
	    $this->_headerText = Mage::helper('customreport')->__('Pickup Orders');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}