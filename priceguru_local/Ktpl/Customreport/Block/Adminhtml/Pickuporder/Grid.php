<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('pickuporderGrid');
      $this->setDefaultSort('created_date');
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
      //$collection->getSelect()->joinleft('sales_flat_pickuporder', 'main_table.increment_id = sales_flat_pickuporder.order_id && main_table.product_sku = sales_flat_pickuporder.sku',array('wholesale_price','qty'));
      $collection->getSelect()->joinleft('sales_flat_order', 'main_table.real_order_id = sales_flat_order.entity_id',array('customer_firstname','customer_email','billing_address_id','total_qty_ordered'));
      //$collection->getSelect()->joinleft('sales_flat_order_address', 'sales_flat_order.entity_id = sales_flat_order_address.parent_id',array('street','city'));
      //$collection->getSelect()->distinct();
      //echo '<pre />'; print_r($collection->getData()); exit;
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
    $this->addColumn('po_comment', array(
      'header'    => Mage::helper('customreport')->__('Po Comment'),
      'width'     => '75px',
      'index'     => 'po_comment',
    ));
    
    $this->addColumn('created_date', array(
      'header'    => Mage::helper('customreport')->__('created_date'),
      'width'     => '75px',
      'index'     => 'created_date',
      'column_css_class'=>'no-display',//this sets a css class to the column row item
      'header_css_class'=>'no-display',  
    ));
    
   /* $this->addColumn('repair_id', array(
      'header'    => Mage::helper('customreport')->__('Repair Id #'),
      'width'     => '75px',
      'index'     => 'repair_id',
    )); */

    $this->addColumn('order_created_date', array(
        'header' => Mage::helper('customreport')->__('Invoice On'),
        'width' => '100px',
        'index' => 'order_created_date',
        'type' => 'datetime',
        
    ));

    $this->addColumn('customer_name', array(
        'header'    => Mage::helper('customreport')->__('Customer Name'),
        'width'     => '100px',     
        'align'     =>'left',
        'index'     => 'customer_name',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_customername',
    ));
   /* $this->addColumn('customer_email', array(
        'header'    => Mage::helper('customreport')->__('Customer Email'),
        'width'     => '100px', 
        'align'     =>'left',
        'index'     => 'customer_email',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_customeremail',
    )); 
    $this->addColumn('telephone', array(
        'header'    => Mage::helper('customreport')->__('Customer Phone'),
        'width'     => '100px',   
        'align'     =>'left',
        'index'     => 'telephone',
    )); 
    
    $this->addColumn('city', array(
        'header'    => Mage::helper('customreport')->__('City'),
        'align'     =>'left',
        'width'     => '400px',            
        'index'     => 'city',
         'filter'    => false,
        'sortable'  => false,
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_city',
    )); 
    
    $this->addColumn('product_name', array(
        'header'    => Mage::helper('customreport')->__('Product name'),
        'align'     =>'left',
     		'width'     => '400px',       		 
        'index'     => 'product_name',
    ));*/

    $this->addColumn('sku', array(
          'header'    => Mage::helper('customreport')->__('SKU'),
          'align'     =>'left',
          'index'     => 'sku',
          'width'     => '100px', 
    ));

    $this->addColumn('qty', array(
        'header'    => Mage::helper('customreport')->__('Qty'),
        'align'     =>'center',
        'index'     => 'qty',
    ));
    $this->addColumn('total_qty_ordered', array(
        'header'    => Mage::helper('customreport')->__('Total Products'),
        'align'     =>'center',
        'index'     => 'total_qty_ordered',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_totalqty',
    ));

    $this->addColumn('attributes', array(
        'header'    => Mage::helper('customreport')->__('Attributes'),
        'align'     =>'left',
        'index'     => 'attributes',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_attributes',
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
    ));

    $this->addColumn('wholesale_price', array(
        'header'    => Mage::helper('customreport')->__('Wholesale Price'),
        'align'     =>'left',
        'index'     => 'wholesale_price',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_wholesaleprice',
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
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_markup',
    )); */
      
    $this->addColumn('wholesaler_id', array(
      'header'    => Mage::helper('customreport')->__('Wholesaler'),
      'align'     =>'left',
      'index'     => 'wholesaler_id',
      'type'      => 'options',
      'options' => Mage::getSingleton('customreport/order_config')->getWholesaler(),
      'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_wholesaler',
    ));

    $this->addColumn('pickup_address', array(
        'header'    => Mage::helper('customreport')->__('Wholesaler Address'),
        'align'     =>'left',
        'index'     => 'pickup_address',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_pickupaddress',
    ));

  /*  $this->addColumn('payment_method', array(
        'header'    => Mage::helper('customreport')->__('Payment Method'),
        'align'     =>'left',
        'index'     => 'payment_method',
    )); */

   /* $this->addColumn('pickup', array(
        'header'    => Mage::helper('customreport')->__('Pickup'),
        'align'     =>'center',
        'index'     => 'pickup',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_pickup',
    ));*/
    
   /* $this->addColumn('client_connected', array(
        'header'    => Mage::helper('customreport')->__('Client Contacted'),
        'align'     =>'center',
        'index'     => 'client_connected',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_clientconnect',
    )); */
    $this->addColumn('pickup_date', array(
        'header' => Mage::helper('customreport')->__('Pick up Date'),
        'index' => 'pickup_date',
        'type' => 'datetime',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_pickupdate',
    )); 
    
    $this->addColumn('pickup_by', array(
        'header'    => Mage::helper('customreport')->__('Pickup By'),
        'align'     =>'left',
        'width'     => '400px',            
        'index'     => 'pickup_by',        
        'type'      => 'options',
        'options'   => Mage::getSingleton('customreport/pickupby')->getOptionArray(),
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_pickupby'
    )); 
    
    $this->addColumn('pickup_done', array(
        'header'    => Mage::helper('customreport')->__('Pickup Done'),
        'align'     =>'left',
        'index'     => 'pickup_done',
        'type'      => 'options',
        'options'   => array(
              2 => 'No',
              1 => 'Yes',
              3 => 'Waiting for stock'
          ),
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_Pickupdone',
    )); 
    
    $this->addColumn('pickup_comment', array(
     		'header'    => Mage::helper('customreport')->__('Pickup Comment'),
     		'align'     =>'left',
     		'width'     => '400px',
     		'filter'    => false,
        'sortable'  => false,
        'index'     => 'pickup_comment',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_pickupcomment',
    ));

  /*  $this->addColumn('delivery', array(
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
    )); 
    $this->addColumn('region', array(
        'header' => Mage::helper('customreport')->__('Region'),
        'index' => 'region',
        'type'  => 'options',
        'options'   => array(
              1 => '1',
              2 => '2',
              3 => '3A',
              7 => '3B', 
              4 => '4',
              5 => '5',
              6 => '6A',
              8 => '6B',
            
          ),
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_region'
    )); */
    $this->addColumn('delivery_date', array(
        'header' => Mage::helper('customreport')->__('Delivery Date'),
        'index' => 'delivery_date',
        'type' => 'datetime',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_deliverydate',
    )); 
    $this->addColumn('address', array(
        'header'    => Mage::helper('customreport')->__('Address'),
        'align'     =>'left',
        'width'     => '500px',            
        'index'     => 'street',
         'filter'    => false,
        'sortable'  => false,
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_address',
    ));
    $this->addColumn('status', array(
        'header' => Mage::helper('customreport')->__('Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
        'filter_index'=>'main_table.status',
        'options'   => array(
              1 => 'Pending',
              2 => 'Cancel',
              3 => 'Complete',
              4 => 'Waiting for stock'
          ),
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_status',
    ));

    

    $this->addColumn('button', array(
        'header' => Mage::helper('customreport')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'pickup_id',
        'is_system'   => true,
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_button'
    ));

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
    
    $this->addColumn('leadtime', array(
        'header'    => Mage::helper('customreport')->__('Leadtime'),
        'align'     =>'left',
        'width'     => '400px',
        'index'     => 'leadtime',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_leadtime',
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