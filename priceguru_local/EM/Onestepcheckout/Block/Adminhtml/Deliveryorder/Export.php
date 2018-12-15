<?php

class EM_Onestepcheckout_Block_Adminhtml_Deliveryorder_Export extends Mage_Adminhtml_Block_Widget_Grid
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
      $collection = Mage::getModel('onestepcheckout/salesdeliveryorder')->getCollection();
      $collection->addFieldToFilter('status', array('neq' => '0'));
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
        'header' => Mage::helper('onestepcheckout')->__('Purchased On'),
        'index' => 'order_created_date',
        'type' => 'datetime',
        'width' => '100px',
    ));

    $this->addColumn('customer_name', array(
		  'header'    => Mage::helper('onestepcheckout')->__('Client Name'),
		  'index'     => 'customer_name',
    ));
	  
    $this->addColumn('telephone', array(
        'header'    => Mage::helper('onestepcheckout')->__('Telephone'),
        'align'     =>'left',
        'index'     => 'telephone',
    ));

    $this->addColumn('address', array(
      'header'    => Mage::helper('onestepcheckout')->__('Address'),
      'index'     => 'address',
    ));

    $this->addColumn('region', array(
        'header' => Mage::helper('onestepcheckout')->__('Region'),
        'index' => 'region',
        'type'  => 'options',
        'options'   => array(
              1 => '1',
              2 => '2',
              3 => '3',
              4 => '4',
              5 => '5'
          ),
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
    ));

    $this->addColumn('payment_method', array(
        'header'    => Mage::helper('onestepcheckout')->__('Payment Method'),
        'align'     =>'left',
        'index'     => 'payment_method',
    ));

    $this->addColumn('deposit', array(
        'header'    => Mage::helper('onestepcheckout')->__('CIM Deposit'),
        'align'     =>'left',
        'index'     => 'deposit',
    ));
      
    $this->addColumn('customer_comment', array(
     		'header'    => Mage::helper('onestepcheckout')->__('Customer Comment'),
     		'align'     =>'left',
     		'width'     => '400px',
     		'filter'    => false,
        'sortable'  => false,
        'index'     => 'customer_comment',
    ));

    $this->addColumn('delivery_comment', array(
        'header'    => Mage::helper('onestepcheckout')->__('Delivery Comment'),
        'align'     =>'left',
        'width'     => '400px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'delivery_comment',
    ));

    $this->addColumn('delivery_date_time', array(
        'header' => Mage::helper('onestepcheckout')->__('Delivery Date'),
        'index' => 'delivery_date_time',
        'type' => 'datetime',
    ));

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
    ));

    $this->addColumn('delivery_time', array(
        'header' => Mage::helper('onestepcheckout')->__('Delivery Time'),
        'index' => 'delivery_time',
    ));

    /*$this->addColumn('button', array(
        'header' => Mage::helper('onestepcheckout')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'delivery_id',
        'renderer'  => 'onestepcheckout/adminhtml_deliveryorder_grid_renderer_button'
    ));*/

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