<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Contact extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $url = Mage::helper("adminhtml")->getUrl("eshopsync/adminhtml_customer/index/");
    $this->_controller = 'adminhtml_contact';
    $this->_blockGroup = 'eshopsync';
    $this->_headerText = Mage::helper('eshopsync')->__('Salesforce Contact Mapping');
    $this->_addButtonLabel = Mage::helper('eshopsync')->__('Add Item');
    $this->addButton('back', array(
        'label'   => $this->__('Back'),
        'onclick' => "setLocation('{$url}')",
        'class'   => 'back'
    ));
    parent::__construct();
    $this->_removeButton("add");
  }
}
