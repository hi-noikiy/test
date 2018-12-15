<?php

class EM_Slideshow3_Block_Adminhtml_Slider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('slideshow3_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('slideshow3')->__('Slideshow Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_general', array(
          'label'     => Mage::helper('slideshow3')->__('General'),
          'title'     => Mage::helper('slideshow3')->__('General'),
          'content'   => $this->getLayout()->createBlock('slideshow3/adminhtml_slider_edit_tab_general')->toHtml(),
      ));
	  $this->addTab('form_images', array(
          'label'     => Mage::helper('slideshow3')->__('Images'),
          'title'     => Mage::helper('slideshow3')->__('Images'),
          'content'   => $this->getLayout()->createBlock('slideshow3/adminhtml_slider_edit_tab_images')->toHtml(),
      ));
	  
     
      return parent::_beforeToHtml();
  }
}