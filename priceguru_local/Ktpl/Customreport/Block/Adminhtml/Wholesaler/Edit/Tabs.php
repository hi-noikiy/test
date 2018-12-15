<?php

class Ktpl_Customreport_Block_Adminhtml_Wholesaler_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('wholesaler_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('customreport')->__('Wholesale Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('customreport')->__('Wholesale details'),
          'title'     => Mage::helper('customreport')->__('Wholesale details'),
          'content'   => $this->getLayout()->createBlock('customreport/adminhtml_wholesaler_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}