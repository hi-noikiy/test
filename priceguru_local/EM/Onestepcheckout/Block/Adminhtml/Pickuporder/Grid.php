<?php

class EM_Onestepcheckout_Block_Adminhtml_Pickuporder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('pickuporderGrid');
      $this->setDefaultSort('order_id');
      $this->setDefaultDir('DESC');
      $this->setDefaultFilter(array('pickup' => 0));
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

    $this->addColumn('order_created_date', array(
        'header' => Mage::helper('onestepcheckout')->__('Invoice On'),
        'index' => 'order_created_date',
        'type' => 'datetime',
        'width' => '100px',
    ));

    $this->addColumn('product_name', array(
        'header'    => Mage::helper('onestepcheckout')->__('Product name'),
        'align'     =>'left',
     		'width'     => '400px',       		 
        'index'     => 'product_name',
    ));

    $this->addColumn('sku', array(
          'header'    => Mage::helper('onestepcheckout')->__('SKU'),
          'align'     =>'left',
          'index'     => 'sku',
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
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_attributes',
    ));

    /*$this->addColumn('payment_method', array(
        'header'    => Mage::helper('onestepcheckout')->__('Payment Method'),
        'align'     =>'left',
        'index'     => 'payment_method',
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_payment',
    ));

    $this->addColumn('deposit', array(
        'header'    => Mage::helper('onestepcheckout')->__('CIM Deposit'),
        'align'     =>'left',
        'index'     => 'deposit',
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_deposit',
    ));*/

    $this->addColumn('wholesale_price', array(
        'header'    => Mage::helper('onestepcheckout')->__('Wholesale Price'),
        'align'     =>'left',
        'index'     => 'wholesale_price',
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_wholesaleprice',
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
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_markup',
    ));
      
    $this->addColumn('wholesaler_id', array(
      'header'    => Mage::helper('onestepcheckout')->__('Wholesaler'),
      'align'     =>'left',
      'index'     => 'wholesaler_id',
      'type'      => 'options',
      'options' => Mage::getSingleton('onestepcheckout/order_config')->getWholesaler(),
      'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_wholesaler',
    ));

    $this->addColumn('pickup_address', array(
        'header'    => Mage::helper('onestepcheckout')->__('Wholesaler Address'),
        'align'     =>'left',
        'index'     => 'pickup_address',
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_pickupaddress',
    ));

    $this->addColumn('payment_method', array(
        'header'    => Mage::helper('onestepcheckout')->__('Payment Method'),
        'align'     =>'left',
        'index'     => 'payment_method',
    ));

    $this->addColumn('pickup', array(
        'header'    => Mage::helper('onestepcheckout')->__('Pickup'),
        'align'     =>'center',
        'index'     => 'pickup',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_pickup',
    ));
      
    /*$this->addColumn('pickup_comment', array(
     		'header'    => Mage::helper('onestepcheckout')->__('Pickup Comment'),
     		'align'     =>'left',
     		'width'     => '400px',
     		'filter'    => false,
        'sortable'  => false,
        'index'     => 'pickup_comment',
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_pickupcomment',
    ));

    $this->addColumn('delivery', array(
        'header'    => Mage::helper('onestepcheckout')->__('Delivery'),
        'align'     =>'left',
        'index'     => 'delivery',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              2 => 'No',
          ),
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_delivery',
    ));

    $this->addColumn('delivery_comment', array(
        'header'    => Mage::helper('onestepcheckout')->__('Delivery Comment'),
        'align'     =>'left',
        'width'     => '400px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'delivery_comment',
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_deliverycomment',
    ));*/

    $this->addColumn('status', array(
        'header' => Mage::helper('onestepcheckout')->__('Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
        'options'   => array(
              1 => 'Pending',
              2 => 'Cancel',
              3 => 'Complete',
              4 => 'On Hold'
          ),
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_status',
    ));

    /*$this->addColumn('delivery_time', array(
        'header' => Mage::helper('onestepcheckout')->__('Delivery Time'),
        'index' => 'delivery_time',
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_deliverytime',
    ));*/

    $this->addColumn('button', array(
        'header' => Mage::helper('onestepcheckout')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'pickup_id',
        'renderer'  => 'onestepcheckout/adminhtml_pickuporder_grid_renderer_button'
    ));

    $this->addColumn('action',
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
    ));
       
	  $this->addExportType('*/*/exportCsv', Mage::helper('ordercustomer')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('ordercustomer')->__('XML'));
	  
      return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
  
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