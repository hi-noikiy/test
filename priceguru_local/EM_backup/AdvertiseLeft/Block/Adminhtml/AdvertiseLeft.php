<?php
class EM_AdvertiseLeft_Block_Adminhtml_AdvertiseLeft extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_advertiseleft';
    $this->_blockGroup = 'advertiseleft';
    $this->_headerText = Mage::helper('advertiseleft')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('advertiseleft')->__('Add Item');
    parent::__construct();
  }
}