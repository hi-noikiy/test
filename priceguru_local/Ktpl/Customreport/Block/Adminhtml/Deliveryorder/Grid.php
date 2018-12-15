<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('deliveryorderGrid');
      $this->setDefaultSort('order_id');
      $this->setDefaultDir('DESC');
      $this->setDefaultFilter(array('status' => 1));
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
      $collection = Mage::getModel('customreport/salesdeliveryorder')->getCollection();
      $collection->addFieldToFilter('main_table.status', array('neq' => '0'));
      $collection->getSelect()->join('sales_flat_order', 'main_table.order_id = sales_flat_order.increment_id',array('entity_id','total_qty_ordered','subtotal','order_currency_code','status as ost'));
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

  /*  $this->addColumn('order_created_date', array(
        'header' => Mage::helper('customreport')->__('Purchased On'),
        'index' => 'order_created_date',
        'type' => 'datetime',
        'width' => '100px',
    )); */
     $this->addColumn('payment_method', array(
        'header'    => Mage::helper('customreport')->__('Payment Method'),
        'align'     =>'left',
        'index'     => 'payment_method',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_payment',
    )); 

    $this->addColumn('repair_id', array(
      'header'    => Mage::helper('customreport')->__('Repair no'),
      'width'     => '75px',
      'index'     => 'repair_id',
    ));
     
    $this->addColumn('pickup_status', array(
      'header'    => Mage::helper('customreport')->__('Pickup Done'),
      'width'     => '75px',
      'align'     =>'center',  
      'index'     => 'pickup_status',
      'type'  => 'options',  
      'options'   => array(
              2 => 'No',
              1 => 'Yes',
              3 => 'Waiting for stock',
          ),  
      'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_pickupstatus',  
    ));
     
    $this->addColumn('pickup_date', array(
      'header'    => Mage::helper('customreport')->__('Pickup Date'),
      'index'     => 'pickup_date',
      'type'      => 'date',
      'format'    => 'yyyy-MM-dd',
    ));
     
    $this->addColumn('pickup_comment', array(
      'header'    => Mage::helper('customreport')->__('Pickup Comment'),
      'width'     => '75px',
      'index'     => 'pickup_comment',
    ));
    
    $this->addColumn('customer_comment', array(
     		'header'    => Mage::helper('customreport')->__('Customer Comment'),
     		'align'     =>'left',
     		'width'     => '400px',
     		'filter'    => false,
        'sortable'  => false,
        'index'     => 'customer_comment',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_customercomment',
    ));

    $this->addColumn('customer_name', array(
		  'header'    => Mage::helper('customreport')->__('Client Name'),
		  'index'     => 'customer_name',
        'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_customername',
    ));
	  
    $this->addColumn('telephone', array(
        'header'    => Mage::helper('customreport')->__('Telephone'),
        'align'     =>'left',
        'index'     => 'telephone',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_telephone'
    ));

    $this->addColumn('address', array(
      'header'    => Mage::helper('customreport')->__('Address'),
      'index'     => 'address',
      'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_address'
    ));
    
    $this->addColumn('latitude', array(
      'header'    => Mage::helper('customreport')->__('Latitude'),
      'index'     => 'latitude',
      'filter'    => false,
      'sortable'  => false,  
      'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_latitude'
    ));
    
    $this->addColumn('longitude', array(
      'header'    => Mage::helper('customreport')->__('Longitude'),
      'index'     => 'longitude',
      'filter'    => false,
      'sortable'  => false,  
      'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_longitude'
    ));
    
    
    $this->addColumn('client_connected', array(
        'header'    => Mage::helper('customreport')->__('Client Contacted'),
        'align'     =>'center',
        'index'     => 'client_connected',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              2 => 'No',
          ),
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_clientconnect',
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
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_region'
    ));
    
     $this->addColumn('delivery_date_time', array(
        'header' => Mage::helper('customreport')->__('Delivery Date'),
        'index' => 'delivery_date_time',
        'type' => 'datetime',
        'width' => '100px',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_deliverydate',
    ));
    
    $this->addColumn('del_time', array(
        'header' => Mage::helper('customreport')->__('Start Time'),
        'index' => 'del_time',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_deliverytime',
    ));
    $this->addColumn('del_time2', array(
        'header' => Mage::helper('customreport')->__('End Time'),
        'index' => 'del_time2',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_deliverytime2',
    ));
    
    $this->addColumn('city', array(
        'header'    => Mage::helper('customreport')->__('City'),
        'align'     =>'left',
        'width'     => '100px',            
        'index'     => 'city',
         'filter'    => false,
        'sortable'  => false,
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_city',
        
    ));
    
    $this->addColumn('button1', array(
        'header' => Mage::helper('customreport')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'delivery_id',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_button'
    ));
    
    $this->addColumn('delivery_comment', array(
        'header'    => Mage::helper('customreport')->__('Delivery Comment'),
        'align'     =>'left',
        'width'     => '400px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'delivery_comment',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_deliverycomment',
    ));
    
    $this->addColumn('comment_history', array(
        'header'    => Mage::helper('customreport')->__('Delivery Comment History'),
        'align'     =>'left',
        'width'     => '40%',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'comment_history',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_commenthistory',
    ));

 /*   $this->addColumn('product_name', array(
        'header'    => Mage::helper('customreport')->__('Product name'),
        'align'     =>'left',
     		'width'     => '400px',       		 
        'index'     => 'product_name',
    )); */

    $this->addColumn('sku', array(
          'header'    => Mage::helper('customreport')->__('SKU'),
          'align'     =>'left',
          'index'     => 'sku',
    ));

    $this->addColumn('attributes', array(
        'header'    => Mage::helper('customreport')->__('Attributes'),
        'align'     =>'left',
        'index'     => 'attributes',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_attributes',
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

    

       $this->addColumn('deposit', array(
        'header'    => Mage::helper('customreport')->__('CIM Deposit'),
        'align'     =>'left',
        'index'     => 'deposit',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_deposit',
    ));
    $this->addColumn('subtotal', array(
        'header'    => Mage::helper('customreport')->__('Retail Price'),
        'align'     =>'left',
        'index'     => 'subtotal',
        'type' => 'currency',
         'currency_code' => 'MUR',
        //'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_deposit',
    ));

    $this->addColumn('delivered_by', array(
        'header' => Mage::helper('customreport')->__('Delivered by'),
        'index' => 'delivered_by',
        'type'  => 'options',
        'width' => '70px',
       // 'filter_index'=>'main_table.status',
        'options'   => array(
              1 => '1124FB16',
              2 => '5744AG18',
              3 => 'YANNICK CAR',
              4 => '731ZY09',
              5 => '8713DC15',
              6 => 'CAR RENTAL 1',
              7 => 'CAR RENTAL 2',
          ),
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_delivered',
    ));

    $this->addColumn('status', array(
        'header' => Mage::helper('customreport')->__('Delivery Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
        'filter_index'=>'main_table.status',
        'options'   => array(
              1 => 'Pending',
              2 => 'Cancel',
              3 => 'Complete',
              4 => 'On Hold'
          ),
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_status',
    )); 
    $this->addColumn('order_status', array(
        'header' => Mage::helper('customreport')->__('Status'),
        'index' => 'order_status',
        'type'  => 'options',
        'width' => '70px',
        'filter_index'=>'sales_flat_order.status',
        'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        //'filter_condition_callback' => array($this, '_filterStoreCondition'),
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_orderstatus',
    )); 

    $this->addColumn('button', array(
        'header' => Mage::helper('customreport')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'delivery_id',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_button'
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
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_leadtime',
    ));
    
    $this->addColumn('full_leadtime', array(
        'header'    => Mage::helper('customreport')->__('Full Leadtime'),
        'align'     =>'left',
        'width'     => '400px',
        'index'     => 'full_leadtime',
        'renderer'  => 'customreport/adminhtml_deliveryorder_grid_renderer_fulltime',
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