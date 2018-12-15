<?php
class EM_Slideshow3_Block_Adminhtml_Slider3 extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_slider3';
    $this->_blockGroup = 'slideshow3';
    $this->_headerText = Mage::helper('slideshow3')->__('Item Manager123');
    $this->_addButtonLabel = Mage::helper('slideshow3')->__('Add Item');
    parent::__construct();
  }
}