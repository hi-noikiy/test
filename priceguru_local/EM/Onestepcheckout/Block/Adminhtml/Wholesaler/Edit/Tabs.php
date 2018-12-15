<?php

class EM_Onestepcheckout_Block_Adminhtml_Wholesaler_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('wholesaler_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('onestepcheckout')->__('Wholesale Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('onestepcheckout')->__('Wholesale details'),
          'title'     => Mage::helper('onestepcheckout')->__('Wholesale details'),
          'content'   => $this->getLayout()->createBlock('onestepcheckout/adminhtml_wholesaler_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}