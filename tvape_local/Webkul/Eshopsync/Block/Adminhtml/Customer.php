<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Customer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_customer';
    $this->_blockGroup = 'eshopsync';
    $this->_headerText = Mage::helper('eshopsync')->__('Salesforce Customer Mapping');
    $this->_addButtonLabel = Mage::helper('eshopsync')->__('Add Item');

    $this->_addButton('viewall', array(
        'label'     => Mage::helper('eshopsync')->__('View All'),
        'onclick'   => 'setLocation(\'' . $this->getViewAll() .'\')',
        'class'     => '',
    ));

    $this->_addButton('sync', array(
        'label'     => Mage::helper('eshopsync')->__('Synchronised ('.Mage::helper("eshopsync/data")->getSyncNumber("customer").')'),
        'onclick'   => 'setLocation(\'' . $this->getShowSyncUrl() .'\')',
        'class'     => '',
    ));

    $this->_addButton('unsync', array(
          'label'     => Mage::helper('eshopsync')->__('Unsynchronised ('.$this->getUnsyncNumber().')'),
          'onclick'   => 'setLocation(\'' . $this->getShowUnsyncUrl() .'\')',
          'class'     => '',
    ));

    $this->_addButton('eshopsync_customer_export', array(
    		'label'   => Mage::helper('eshopsync')->__('Synchronize All Customer(s) and Contact(s)'),
    		'class'   => 'eshopsync_customer_export save'
  	));

    parent::__construct();
    $this->_removeButton("add");
  }

  public function getShowSyncUrl(){
    return $this->getUrl('*/*/index/sync/1');
  }

  public function getShowUnsyncUrl(){
    return $this->getUrl('*/*/index/unsync/1');
  }

  public function getViewAll(){
    return $this->getUrl('*/*/index/');
  }

  public function getUnsyncNumber(){
    $collection = Mage::getModel('customer/customer')->getCollection();
    $prefix = Mage::getConfig()->getTablePrefix();
    $collection->getSelect()->joinLeft(
        array('cus' => $prefix."wk_salesforce_eshopsync_customer_mapping"),
        'cus.magento_id = e.entity_id',
        array('magento_id','sforce_id','created_by','created_at','error_hints')
    );
    $collection->getSelect()->where("cus.error_hints is not null or cus.magento_id is null");
    $n = count($collection);
    return $n;
  }
}
