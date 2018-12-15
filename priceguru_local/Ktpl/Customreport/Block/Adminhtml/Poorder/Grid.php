<?php

class Ktpl_Customreport_Block_Adminhtml_Poorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('poorderGrid');
      $this->setDefaultSort('created_date');
      $this->setDefaultDir('DESC');
     // $this->setDefaultFilter(array('pickup' => 0));
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
      $collection = Mage::getModel('customreport/poorder')->getCollection();
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
    
    $this->addColumn('updated_date', array(
      'header'    => Mage::helper('customreport')->__('created_date'),
      'width'     => '75px',
      'index'     => 'updated_date',
      'column_css_class'=>'no-display',//this sets a css class to the column row item
      'header_css_class'=>'no-display',  
    ));

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
        //'renderer'  => 'customreport/adminhtml_pickuporder_grid_renderer_customername',
    ));
    
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
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_attributes',
    ));
  

    $this->addColumn('wholesale_price', array(
        'header'    => Mage::helper('customreport')->__('Wholesale Price'),
        'align'     =>'left',
        'index'     => 'wholesale_price',
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_wholesaleprice',
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
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_markup',
    ));
      
    $this->addColumn('wholesaler_id', array(
      'header'    => Mage::helper('customreport')->__('Wholesaler'),
      'align'     =>'left',
      'index'     => 'wholesaler_id',
      'type'      => 'options',
      'options' => Mage::getSingleton('customreport/order_config')->getWholesaler(),
      'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_wholesaler',
    ));

    $this->addColumn('pickup_address', array(
        'header'    => Mage::helper('customreport')->__('Wholesaler Address'),
        'align'     =>'left',
        'index'     => 'pickup_address',
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_pickupaddress',
    ));

    $this->addColumn('payment_method', array(
        'header'    => Mage::helper('customreport')->__('Payment Method'),
        'align'     =>'left',
        'index'     => 'payment_method',
    ));

    $this->addColumn('po_comment', array(
        'header'    => Mage::helper('customreport')->__('Po Comment'),
        'align'     =>'left',
        'width'     => '400px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'po_comment',
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_pocomment',
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
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_status',
    ));

    

    $this->addColumn('button', array(
        'header' => Mage::helper('customreport')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'pickup_id',
        'is_system'   => true,
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_button'
    ));
    
    $this->addColumn('inventory', array(
        'header' => Mage::helper('customreport')->__('Inventory'),
        'index' => 'inventory',
        'type'  => 'options',
        'width' => '70px',
        'filter_index'=>'inventory',
        'options'   => array(
              1 => '',
              2 => 'Yes',
              3 => 'Credit Note',
              
          ),
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_inventory',
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
        'renderer'  => 'customreport/adminhtml_poorder_grid_renderer_leadtime',
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