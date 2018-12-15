<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_deliveryorder';
	    $this->_blockGroup = 'customreport';
	    $this->_headerText = Mage::helper('customreport')->__('Delivery Orders');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}