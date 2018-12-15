<?php
class EM_Link_Block_Adminhtml_Link extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_link';
    $this->_blockGroup = 'link';
    $this->_headerText = Mage::helper('link')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('link')->__('Add Item');
    parent::__construct();
  }
}