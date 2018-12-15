<?php

class Ktpl_Repaircenter_Block_Adminhtml_Servicecenter extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_servicecenter';
    $this->_blockGroup = 'repaircenter';
    $this->_headerText = Mage::helper('repaircenter')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('repaircenter')->__('Add Service Center');
    parent::__construct();
  }
}