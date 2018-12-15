<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircustomer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_repaircustomer';
	    $this->_blockGroup = 'repaircenter';
	    $this->_headerText = Mage::helper('repaircenter')->__('Repair to Customer');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}