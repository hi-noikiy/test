<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Contactus extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_contactus';
    $this->_blockGroup = 'eshopsync';
    $this->_headerText = Mage::helper('eshopsync')->__('Salesforce Lead Mapping');
    $this->_addButtonLabel = Mage::helper('eshopsync')->__('Add Item');
    $this->_addButtonLabel = Mage::helper('eshopsync')->__('Add Item');
    // $this->_addButton('eshopsync_lead_export', array(
    // 		'label'   => Mage::helper('eshopsync')->__('Export All Lead(s)'),
    // 		'class'   => 'eshopsync_lead_export save'
  	// ));
    parent::__construct();
    $this->_removeButton("add");
  }
}
