<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_order';
    $this->_blockGroup = 'eshopsync';

    $this->_addButton('viewall', array(
        'label'     => Mage::helper('eshopsync')->__('View All'),
        'onclick'   => 'setLocation(\'' . $this->getViewAll() .'\')',
        'class'     => '',
    ));

    $this->_addButton('sync', array(
        'label'     => Mage::helper('eshopsync')->__('Synchronised ('.Mage::helper("eshopsync/data")->getSyncNumber("order").')'),
        'onclick'   => 'setLocation(\'' . $this->getShowSyncUrl() .'\')',
        'class'     => '',
    ));

    $this->_addButton('unsync', array(
          'label'     => Mage::helper('eshopsync')->__('Unsynchronised ('.$this->getUnsyncNumber().')'),
          'onclick'   => 'setLocation(\'' . $this->getShowUnsyncUrl() .'\')',
          'class'     => '',
    ));

    $this->_headerText = Mage::helper('eshopsync')->__('Salesforce Order Mapping');
    $this->_addButtonLabel = Mage::helper('eshopsync')->__('Add Item');
    $this->_addButton('eshopsync_order_export', array(
    		'label'   => Mage::helper('eshopsync')->__('Export All Order(s)'),
    		'class'   => 'eshopsync_order_export save'
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
    $collection = Mage::getModel('sales/order')->getCollection();
    $prefix = Mage::getConfig()->getTablePrefix();
    $collection->getSelect()->joinLeft(
      array('order' => $prefix.'wk_salesforce_eshopsync_order_mapping'),
      'order.magento_id = main_table.entity_id',
      array('magento_id','sforce_id','account_id','created_at','error_hints')
    );
    $collection->getSelect()->where("order.error_hints is not null or order.magento_id is null");
    $n = count($collection);
    return $n;
  }

}
