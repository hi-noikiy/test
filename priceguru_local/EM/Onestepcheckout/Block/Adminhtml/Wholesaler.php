<?php
class EM_Onestepcheckout_Block_Adminhtml_Wholesaler extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_wholesaler';
    $this->_blockGroup = 'onestepcheckout';
    $this->_headerText = Mage::helper('onestepcheckout')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('onestepcheckout')->__('Add Wholesaler');
    parent::__construct();
  }
}