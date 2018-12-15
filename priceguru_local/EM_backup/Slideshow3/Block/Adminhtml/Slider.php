<?php
class EM_Slideshow3_Block_Adminhtml_Slider extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_slider';
    $this->_blockGroup = 'slideshow3';
    $this->_headerText = Mage::helper('slideshow3')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('slideshow3')->__('Add Item');
    parent::__construct();
  }
}