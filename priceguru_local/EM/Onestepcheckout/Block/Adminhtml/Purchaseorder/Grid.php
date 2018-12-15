<?php

class EM_Onestepcheckout_Block_Adminhtml_Purchaseorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('purchaseorderGrid');
      $this->setDefaultSort('order_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _getStore()
  {
      $storeId = (int) $this->getRequest()->getParam('store', 0);
      return Mage::app()->getStore($storeId);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('onestepcheckout/salespickuporder')->getCollection();
      $collection->addFieldToFilter('po_created', array('eq' => 1));
      //$collection->getSelect()->join('sales_flat_order', 'main_table.order_id = sales_flat_order.increment_id',array('entity_id','status','subtotal','order_currency_code'));
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

    $this->addColumn('order_id', array(
      'header'    => Mage::helper('onestepcheckout')->__('Order #'),
      'width'     => '75px',
      'index'     => 'order_id',
    ));

    $this->addColumn('created_date', array(
        'header' => Mage::helper('onestepcheckout')->__('Invoice Date'),
        'index' => 'created_date',
        'type' => 'datetime',
        'width' => '100px',
    ));

    $this->addColumn('product_name', array(
        'header'    => Mage::helper('onestepcheckout')->__('Product name'),
        'align'     =>'left',    		 
        'index'     => 'product_name',
    ));

    $this->addColumn('sku', array(
          'header'    => Mage::helper('onestepcheckout')->__('SKU'),
          'align'     =>'left',
          'index'     => 'sku',
          'width'     => '400px',
    ));

    $this->addColumn('qty', array(
        'header'    => Mage::helper('onestepcheckout')->__('Qty'),
        'align'     =>'center',
        'index'     => 'qty',
    ));

    $this->addColumn('attributes', array(
        'header'    => Mage::helper('onestepcheckout')->__('Attributes'),
        'align'     =>'left',
        'index'     => 'attributes',
    ));

    $this->addColumn('wholesale_price', array(
        'header'    => Mage::helper('onestepcheckout')->__('Wholesale Price'),
        'align'     =>'left',
        'index'     => 'wholesale_price',
        'type'  => 'currency',
        'currency_code' => 'MUR',
    ));

    $this->addColumn('retail_price', array(
        'header'    => Mage::helper('onestepcheckout')->__('Retail Price'),
        'align'     =>'left',
        'index'     => 'retail_price',
        'type'  => 'currency',
        'currency_code' => 'MUR',
    ));
      
    $this->addColumn('markup', array(
     		'header'    => Mage::helper('onestepcheckout')->__('Markup'),
     		'align'     =>'center',
        'index'     => 'markup',
    ));
      
    $this->addColumn('wholesaler_id', array(
      'header'    => Mage::helper('onestepcheckout')->__('Wholesaler'),
      'align'     =>'left',
      'index'     => 'wholesaler_id',
      'type'      => 'options',
      'options' => Mage::getSingleton('onestepcheckout/order_config')->getWholesaler(),
      'renderer'  => 'onestepcheckout/adminhtml_purchaseorder_grid_renderer_wholesaler',
    ));

    /*$this->addColumn('action',
        array(
            'header'    => Mage::helper('onestepcheckout')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'     => 'getRealOrderId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('onestepcheckout')->__('View'),
                    'url'     => array('base'=>'adminhtml/sales_order/view'),
                    'field'   => 'order_id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
    ));*/
       
	  $this->addExportType('*/*/exportCsv', Mage::helper('ordercustomer')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ordercustomer')->__('XML'));
	  
      return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
    $this->setMassactionIdField('pickup_id');
    $this->getMassactionBlock()->setFormFieldName('pickuporder');

    $this->getMassactionBlock()->addItem('delete', array(
         'label'    => Mage::helper('onestepcheckout')->__('Delete'),
         'url'      => $this->getUrl('*/*/massDelete'),
         'confirm'  => Mage::helper('onestepcheckout')->__('Are you sure?')
    ));

    return $this;
  }

  public function getRowUrl($row)
  {
    //return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getEntityId()));
    return false;
  }

  public function getGridUrl()
  {
        return $this->getUrl('*/*/grid', array('_current'=>true));
  }

}