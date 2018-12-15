<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_repaircenter';
	    $this->_blockGroup = 'repaircenter';
	    $this->_headerText = Mage::helper('repaircenter')->__('Repair to Center');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
}