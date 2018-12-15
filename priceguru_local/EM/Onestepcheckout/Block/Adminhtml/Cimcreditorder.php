<?php

class EM_Onestepcheckout_Block_Adminhtml_Cimcreditorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_cimcreditorder';
	    $this->_blockGroup = 'onestepcheckout';
	    $this->_headerText = Mage::helper('onestepcheckout')->__('CIM Credit Orders');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}