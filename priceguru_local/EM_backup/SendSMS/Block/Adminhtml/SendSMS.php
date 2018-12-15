<?php
class EM_SendSMS_Block_Adminhtml_SendSMS extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_sendsms';
    $this->_blockGroup = 'sendsms';
    $this->_headerText = Mage::helper('sendsms')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('sendsms')->__('Add Item');
    parent::__construct();
  }
}