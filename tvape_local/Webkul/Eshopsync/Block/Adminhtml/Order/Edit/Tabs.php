<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Order_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('eshopsync_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('eshopsync')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('eshopsync')->__('Item Information'),
          'title'     => Mage::helper('eshopsync')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('eshopsync/adminhtml_order_edit_tab_form')->toHtml(),
      ));

      return parent::_beforeToHtml();
  }
}
