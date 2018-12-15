<?php
/*
* @copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Outstocknotification_Block_Adminhtml_Outstocknotification_Grid extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
    parent::__construct();
    $this->setId('outstocknotificationGrid');
    $this->setUseAjax(true);
    $this->setDefaultSort('entity_id');
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection() {					    
    $collection = Mage::getModel('productalert/stock')->getCollection();
    $resource = Mage::getSingleton('core/resource');
    $tableName = $resource->getTableName('catalog/product');
    $collection->getSelect()->join($tableName,'product_id=entity_id');
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {       
    $this->addColumn('website_id', array(
    'header'    => Mage::helper('outstocknotification')->__('Website'),
    'align'     => 'left',
    'width'     => '120px',
    'index'     => 'website_id',
    'type'      => 'options',
    'options'   => array(
    1 => 'Main Website'
    ),
    ));

    $this->addColumn('name', array(
    'header'    => Mage::helper('outstocknotification')->__('Product Name'),
    'align'     =>'left',
    'renderer' => 'outstocknotification/adminhtml_outstocknotification_renderer_productName',        
    ));

    $this->addColumn('sku', array(
    'header'    => Mage::helper('outstocknotification')->__('Sku'),
    'align'     =>'left',
    'index'     => 'sku',
    ));
    $this->addColumn('email', array(
    'header'    => Mage::helper('outstocknotification')->__('Email'),
    'align'     =>'left',
    'index'     => 'email',   
    ));

   $this->addColumn('send_count', array(
		'header'    => Mage::helper('outstocknotification')->__('Sent Email'),
		'align'     => 'left',
		'width'     => '80px',
		'index'     => 'send_count',
		'type'      => 'options',
		'options'   => array(
			1 => 'Yes',
			0 => 'No',
		),
		));

    $this->addColumn('add_date', array(
    'header'    => Mage::helper('outstocknotification')->__('Subscribed At'),
    'align'     =>'left',
    'index'     => 'add_date',   
    ));
    $this->addColumn('send_date', array(
    'header'    => Mage::helper('outstocknotification')->__('Emailed At'),
    'align'     =>'left',
    'index'     => 'send_date',   
    ));

    return parent::_prepareColumns();
  } 


  public function getGridUrl()
  {
    return $this->getUrl('*/*/grid', array('_current'=> true));
  }  

}