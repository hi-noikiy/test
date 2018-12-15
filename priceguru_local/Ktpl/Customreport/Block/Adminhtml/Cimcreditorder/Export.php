<?php

class Ktpl_Customreport_Block_Adminhtml_Cimcreditorder_Export extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('cimcreditorderGrid');
      $this->setDefaultSort('order_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _getStore()
  {
      $storeId = (int) $this->getRequest()->getParam('store', 0);
      return Mage::app()->getStore($storeId);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('customreport/salescimorder')->getCollection();
      $collection->getSelect()->join('sales_flat_order', 'main_table.order_id = sales_flat_order.increment_id',array('entity_id','status','subtotal','order_currency_code'));
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $this->addColumn('created_date', array(
        'header' => Mage::helper('customreport')->__('Purchased On'),
        'index' => 'created_date',
        'type' => 'datetime',
        'width' => '100px',
    ));
    
    $this->addColumn('order_id', array(
			'header'    => Mage::helper('customreport')->__('Order #'),
			'width'     => '75px',
			'index'     => 'order_id',
    ));

    $this->addColumn('customer_name', array(
		  'header'    => Mage::helper('customreport')->__('Customer Name'),
		  'index'     => 'customer_name',
		  //'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_username'
    ));
	  
    $this->addColumn('telephone', array(
        'header'    => Mage::helper('customreport')->__('Telephone'),
        'align'     =>'left',
        'index'     => 'telephone',
    ));

    $this->addColumn('email', array(
      'header'    => Mage::helper('customreport')->__('Email'),
      'index'     => 'email',
      //'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_username'
    ));

    $this->addColumn('iscimcustomer', array(
        'header' => Mage::helper('customreport')->__('CIM Customer'),
        'index' => 'iscimcustomer',
        'align' => 'center',
        'type'  => 'options',
        'width' => '50px',
        'options' => Mage::getSingleton('customreport/order_config')->getStatuses(),
    ));

    $this->addColumn('product_name', array(
        'header'    => Mage::helper('customreport')->__('Product name'),
        'align'     =>'left',
     		'width'     => '400px',       		 
        'index'     => 'product_name',
    ));

    $this->addColumn('sku', array(
          'header'    => Mage::helper('customreport')->__('SKU'),
          'align'     =>'left',
          'index'     => 'sku',
    ));

    $this->addColumn('attributes', array(
        'header'    => Mage::helper('customreport')->__('Attributes'),
        'align'     =>'left',
        'index'     => 'attributes',
        //'renderer'  => 'customreport/adminhtml_cimcreditorder_grid_renderer_attributes',
    ));

    $this->addColumn('subtotal', array(
        'header'    => Mage::helper('customreport')->__('Retail Price'),
        'align'     =>'left',
        'index'     => 'subtotal',
        'type'  => 'currency',
        'currency' => 'order_currency_code',
    ));

    $this->addColumn('dcp', array(
        'header'    => Mage::helper('customreport')->__('DCP Amount'),
        'align'     =>'left',
        'index'     => 'dcp',
    ));
      
    $this->addColumn('installments', array(
     		'header'    => Mage::helper('customreport')->__('No. Installments'),
     		'align'     =>'left',
        'index'     => 'installments',
    ));
      
    $this->addColumn('monthly', array(
      'header'    => Mage::helper('customreport')->__('Monthly'),
      'align'     =>'left',
      //'type'  => 'price',
      'width' => '100px',
      'index'     => 'monthly',
      //'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
    ));

    $this->addColumn('deposit', array(
        'header'    => Mage::helper('customreport')->__('Deposit'),
        'align'     =>'left',
        'index'     => 'deposit',
    ));

    $this->addColumn('cpp', array(
        'header'    => Mage::helper('customreport')->__('CPP'),
        'align'     =>'left',
        'index'     => 'cpp',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              2 => 'No',
          ),
    ));

    $this->addColumn('payment', array(
        'header'    => Mage::helper('customreport')->__('Payment'),
        'align'     =>'left',
        'index'     => 'payment',
    ));

    $this->addColumn('app_number', array(
        'header'    => Mage::helper('customreport')->__('App. Number'),
        'align'     =>'left',
        'index'     => 'app_number',
    ));
      
    $this->addColumn('cimcomment', array(
     		'header'    => Mage::helper('customreport')->__('Customer Comment'),
     		'align'     =>'left',
     		'width'     => '400px',
     		'filter'    => false,
        'sortable'  => false,
        'index'     => 'cimcomment'
    ));

    $this->addColumn('pgcomment', array(
        'header'    => Mage::helper('customreport')->__('PG Comment'),
        'align'     =>'left',
        'width'     => '400px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'pgcomment',
    ));

    $this->addColumn('status', array(
        'header' => Mage::helper('customreport')->__('Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
        'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
    ));

    /*$this->addColumn('button', array(
        'header' => Mage::helper('customreport')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'cimorder_id',
        'renderer'  => 'customreport/adminhtml_cimcreditorder_grid_renderer_button'
    ));*/

    $this->addColumn('action',
        array(
            'header'    => Mage::helper('customreport')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'     => 'getEntityId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('customreport')->__('View'),
                    'url'     => array('base'=>'adminhtml/sales_order/view'),
                    'field'   => 'order_id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
    ));
       
	  $this->addExportType('*/*/exportCsv', Mage::helper('customreport')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('customreport')->__('XML'));
	  
      return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
  
  }

  public function getRowUrl($row)
  {
    //return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getEntityId()));
  }

  public function isAllowed() 
  {
    return true;
  }

}