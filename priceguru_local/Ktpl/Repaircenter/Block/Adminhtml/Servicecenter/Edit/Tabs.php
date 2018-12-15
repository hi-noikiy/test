<?php

class Ktpl_Repaircenter_Block_Adminhtml_Servicecenter_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('servicecenter_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('repaircenter')->__('Service center Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('repaircenter')->__('Service center details'),
          'title'     => Mage::helper('repaircenter')->__('Service center details'),
          'content'   => $this->getLayout()->createBlock('repaircenter/adminhtml_servicecenter_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}