<?php

class Ktpl_Customreport_Block_Adminhtml_Cimcreditorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_cimcreditorder';
	    $this->_blockGroup = 'customreport';
	    $this->_headerText = Mage::helper('customreport')->__('CIM Credit Orders');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}