<?php

class Ktpl_Customreport_Block_Adminhtml_Wholesaler extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_wholesaler';
    $this->_blockGroup = 'customreport';
    $this->_headerText = Mage::helper('customreport')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('customreport')->__('Add Wholesaler');
    parent::__construct();
  }
}