<?php
/*
* @copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Outstocknotification_Block_Adminhtml_Productalert_Gridtab extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
    parent::__construct();
    $this->setId('alertStock');
    $this->setDefaultSort('add_date');
    $this->setDefaultSort('DESC');
    $this->setUseAjax(true);
    $this->setFilterVisibility(false);
    $this->setEmptyText(Mage::helper('catalog')->__('There are no customers for this alert.'));
  }

  protected function _prepareCollection() {
    $productId = $this->getRequest()->getParam('id');
    $websiteId = 0;
    if ($store = $this->getRequest()->getParam('store')) {
      $websiteId = Mage::app()->getStore($store)->getWebsiteId();
    }        
    $collection = Mage::getModel('productalert/stock')->getCollection();
    $collection ->addFieldToFilter('product_id', array('product_id' => $productId));
    $this->setCollection($collection);        
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {
    $this->addColumn('stock_firstname', array(
    'header'    => Mage::helper('outstocknotification')->__('First Name'),
    'index'     => 'firstname',
    ));

    $this->addColumn('stock_lastname', array(
    'header'    => Mage::helper('outstocknotification')->__('Last Name'),
    'index'     => 'lastname',
    ));

    $this->addColumn('stock_email', array(
    'header'    => Mage::helper('outstocknotification')->__('Email'),
    'index'     => 'email',
    ));

    $this->addColumn('stock_add_date', array(
    'header'    => Mage::helper('outstocknotification')->__('Date Subscribed'),
    'index'     => 'add_date',
    'type'      => 'date'
    ));

    $this->addColumn('stock_send_date', array(
    'header'    => Mage::helper('outstocknotification')->__('Last Notification'),
    'index'     => 'send_date',
    'type'      => 'date'
    ));

    $this->addColumn('stock_send_count', array(
    'header'    => Mage::helper('outstocknotification')->__('Send Count'),
    'index'     => 'send_count',
    ));

    return parent::_prepareColumns();
  }

  public function getGridUrl() {
    $productId = $this->getRequest()->getParam('id');
    $storeId   = $this->getRequest()->getParam('store', 0);
    if ($storeId) {
      $storeId = Mage::app()->getStore($storeId)->getId();
    }
    return $this->getUrl('outstocknotification/adminhtml_outstocknotification/alertsStockGrid', array(
    'id'    => $productId,
    'store' => $storeId
    ));
  }
}
