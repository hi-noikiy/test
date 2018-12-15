<?php

class EM_Link_Block_Adminhtml_Link_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('link_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('link')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('link')->__('Item Information'),
          'title'     => Mage::helper('link')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('link/adminhtml_link_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}