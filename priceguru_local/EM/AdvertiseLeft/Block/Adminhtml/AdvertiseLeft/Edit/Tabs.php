<?php

class EM_AdvertiseLeft_Block_Adminhtml_AdvertiseLeft_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('advertiseleft_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('advertiseleft')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('advertiseleft')->__('Item Information'),
          'title'     => Mage::helper('advertiseleft')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('advertiseleft/adminhtml_advertiseleft_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}