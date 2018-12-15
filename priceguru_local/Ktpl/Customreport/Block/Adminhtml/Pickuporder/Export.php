<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Export extends Mage_Adminhtml_Block_Widget_Grid
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
      $collection = Mage::getModel('customreport/salespickuporder')->getCollection();
      //$collection->getSelect()->join('sales_flat_order', 'main_table.order_id = sales_flat_order.increment_id',array('entity_id','status','subtotal','order_currency_code'));
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

    $this->addColumn('order_id', array(
      'header'    => Mage::helper('customreport')->__('Order #'),
      'width'     => '75px',
      'index'     => 'order_id',
    ));

    $this->addColumn('order_created_date', array(
        'header' => Mage::helper('customreport')->__('Purchased On'),
        'index' => 'order_created_date',
        'type' => 'datetime',
        'width' => '100px',
    ));

    /*$this->addColumn('customer_name', array(
		  'header'    => Mage::helper('customreport')->__('Client Name'),
		  'index'     => 'customer_name',
    ));
	  
    $this->addColumn('telephone', array(
        'header'    => Mage::helper('customreport')->__('Telephone'),
        'align'     =>'left',
        'index'     => 'telephone',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_telephone'
    ));

    $this->addColumn('address', array(
      'header'    => Mage::helper('customreport')->__('Address'),
      'index'     => 'address',
      'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_address'
    ));

    $this->addColumn('region', array(
        'header' => Mage::helper('customreport')->__('Region'),
        'index' => 'region',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_region'
    ));*/

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

    $this->addColumn('qty', array(
        'header'    => Mage::helper('customreport')->__('Qty'),
        'align'     =>'center',
        'index'     => 'qty',
    ));

    $this->addColumn('attributes', array(
        'header'    => Mage::helper('customreport')->__('Attributes'),
        'align'     =>'left',
        'index'     => 'attributes',
    ));

    /*$this->addColumn('payment_method', array(
        'header'    => Mage::helper('customreport')->__('Payment Method'),
        'align'     =>'left',
        'index'     => 'payment_method',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_payment',
    ));

    $this->addColumn('deposit', array(
        'header'    => Mage::helper('customreport')->__('CIM Deposit'),
        'align'     =>'left',
        'index'     => 'deposit',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_deposit',
    ));*/

    $this->addColumn('wholesale_price', array(
        'header'    => Mage::helper('customreport')->__('Wholesale Price'),
        'align'     =>'left',
        'index'     => 'wholesale_price',
    ));

    $this->addColumn('retail_price', array(
        'header'    => Mage::helper('customreport')->__('Retail Price'),
        'align'     =>'left',
        'index'     => 'retail_price',
        'type'  => 'currency',
        'currency_code' => 'MUR',
    ));
      
    $this->addColumn('markup', array(
     		'header'    => Mage::helper('customreport')->__('Markup'),
     		'align'     =>'center',
        'index'     => 'markup',
    ));
      
    $this->addColumn('wholesaler_id', array(
      'header'    => Mage::helper('customreport')->__('Wholesaler'),
      'align'     =>'left',
      'index'     => 'wholesaler_id',
      'type'      => 'options',
      'options' => Mage::getSingleton('customreport/order_config')->getWholesaler(),
    ));

    $this->addColumn('pickup_address', array(
        'header'    => Mage::helper('customreport')->__('Wholesaler Address'),
        'align'     =>'left',
        'index'     => 'pickup_address',
    ));

    $this->addColumn('payment_method', array(
        'header'    => Mage::helper('customreport')->__('Purchase Order'),
        'align'     =>'left',
        'index'     => 'payment_method',
    ));

    $this->addColumn('pickup', array(
        'header'    => Mage::helper('customreport')->__('Pickup'),
        'align'     =>'center',
        'index'     => 'pickup',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
    ));
      
    /*$this->addColumn('pickup_comment', array(
     		'header'    => Mage::helper('customreport')->__('Pickup Comment'),
     		'align'     =>'left',
     		'width'     => '400px',
     		'filter'    => false,
        'sortable'  => false,
        'index'     => 'pickup_comment',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_pickupcomment',
    ));

    $this->addColumn('delivery', array(
        'header'    => Mage::helper('customreport')->__('Delivery'),
        'align'     =>'left',
        'index'     => 'delivery',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              2 => 'No',
          ),
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_delivery',
    ));

    $this->addColumn('delivery_comment', array(
        'header'    => Mage::helper('customreport')->__('Delivery Comment'),
        'align'     =>'left',
        'width'     => '400px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'delivery_comment',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_deliverycomment',
    ));*/

    $this->addColumn('status', array(
        'header' => Mage::helper('customreport')->__('Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
        'options'   => array(
              1 => 'Pending',
              2 => 'Cancel',
              3 => 'Complete',
              4 => 'On Hold'
          ),
    ));

    /*$this->addColumn('delivery_time', array(
        'header' => Mage::helper('customreport')->__('Delivery Time'),
        'index' => 'delivery_time',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_deliverytime',
    ));

    $this->addColumn('button', array(
        'header' => Mage::helper('customreport')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'pickup_id',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_button'
    ));*/

    $this->addColumn('action',
        array(
            'header'    => Mage::helper('customreport')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'     => 'getRealOrderId',
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
    return false;
  }

  public function getGridUrl()
  {
        return $this->getUrl('*/*/grid', array('_current'=>true));
  }

}