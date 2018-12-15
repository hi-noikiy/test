<?php
/**
 * Contus Support Interactive.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file GCLONE-LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento 1.4.1.1 COMMUNITY edition
 * Contus Support does not guarantee correct work of this package
 * on any other Magento edition except Magento 1.4.1.1 COMMUNITY edition.
 * =================================================================
 */
class Contus_Ordercustomer_Block_Adminhtml_Ordercustomer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('ordercustomerGrid');
      $this->setDefaultSort('created_time');
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
      $collection = Mage::getModel('ordercustomer/ordercustomer')->getCollection();
      //$collection->getSelect()->joinleft('sales_flat_order', 'main_table.increment_id = sales_flat_order.increment_id',array('total_qty_ordered'));
    $collection->getSelect()->joinleft('sales_flat_pickuporder', 'main_table.increment_id = sales_flat_pickuporder.order_id && main_table.product_sku = sales_flat_pickuporder.sku && sales_flat_pickuporder.repair_id NOT LIKE "%_to_customer"',array('wholesale_price','qty'));
      $collection->getSelect()->joinleft('sales_flat_cimorder as sfc', 'main_table.increment_id = sfc.order_id',array('sfc.deposit'));
      $collection->getSelect()->joinleft('wholesaler as wh', 'sales_flat_pickuporder.wholesaler_id  = wh.wholesaler_id',array('wh.name')); 
      $collection->getSelect()->distinct();
      $this->setCollection($collection); 
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    /*$this->addColumn('ordercustomer_id', array(
        'header'    => Mage::helper('ordercustomer')->__('ID'),
        'align'     =>'right',
        'width'     => '50px',
        'index'     => 'ordercustomer_id',
    )); */
    
    $this->addColumn('increment_id', array(
			'header'    => Mage::helper('ordercustomer')->__('Order Id'),
			'width'     => '75px',
			'index'     => 'increment_id',
    ));

    $this->addColumn('created_time', array(
      'header'    => Mage::helper('ordercustomer')->__('Shipment Created Date'),
      'index'     => 'created_time',
      'type'      => 'datetime',
      //'format'    => 'd-M-Y',
    ));

    $this->addColumn('username', array(
		  'header'    => Mage::helper('ordercustomer')->__('User Name'),
		  'width'     => '75px',
		  'index'     => 'username',
		  //'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_username'
    ));

	  
    $this->addColumn('customer_name', array(
        'header'    => Mage::helper('ordercustomer')->__('Customer Name'),
        'align'     =>'left',
        'index'     => 'customer_name',
    ));

    $this->addColumn('product_name', array(
        'header'    => Mage::helper('ordercustomer')->__('Product name'),
        'align'     =>'left',
     		'width'     => '400px',       		 
        'index'     => 'product_name',
    ));

    $this->addColumn('product_subtitle', array(
        'header'    => Mage::helper('ordercustomer')->__('Product Subtitle'),
        'align'     =>'left',
        'width'     => '400px',            
        'index'     => 'product_subtitle',
    ));
      
    $this->addColumn('Custom Option', array(
     		'header'    => Mage::helper('ordercustomer')->__('Custom Option '),
     		'align'     =>'left',
     		'width'     => '400px',
        'index'     => 'customtitle',
    ));
      
      /*$this->addColumn('Custom Option Value', array(
       		'header'    => Mage::helper('ordercustomer')->__('Custom Option Value'),
       		'align'     =>'left',
       		'width'     => '400px',
          'index'     => 'customoptiontitle',
      ));*/

      $this->addColumn('product_sku', array(
       		'header'    => Mage::helper('ordercustomer')->__('Product SKU'),
       		'align'     =>'left',
       		'index'     => 'product_sku',
      ));
      
       $this->addColumn('total_qty_ordered', array(
       		'header'    => Mage::helper('ordercustomer')->__('No. Items'),
       		'align' => 'center',
                'width' => '10px',
       		'index'     => 'qty',
                 'type'      => 'number',
      ));
      
      $this->addColumn('name', array(
       		'header'    => Mage::helper('ordercustomer')->__('Merchant'),
       		'align' => 'center',
                'width' => '10px',
       		'index'     => 'name',
                'filter_index'=>'wh.name',  
      ));
      
      $this->addColumn('wholesale_price', array(
       		'header'    => Mage::helper('ordercustomer')->__('Wholesale Price'),
       		'align'     =>'left',
       		'index'     => 'wholesale_price',
                'type'      => 'price',
                'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
      )); 
   
      
      $this->addColumn('price', array(
        'header'    => Mage::helper('ordercustomer')->__('Price'),
        'align'     =>'left',
        'type'  => 'price',
        'index'     => 'price',
        'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
      ));

      $this->addColumn('delivery', array(
        'header'    => Mage::helper('ordercustomer')->__('Delivery'),
        'align'     =>'center',
        'index'     => 'delivery',
        'filter'  => false,
        'sortable'  => false,  
        'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_delivery'
      )); 
      
      $this->addColumn('rewardpoints_discount', array(
        'header'    => Mage::helper('ordercustomer')->__('Reward Points'),
        'align'     =>'center',
        //'type'  => 'price',
        'index'     => 'rewardpoints_discount',
        'filter'  => false,
        'sortable'  => false,  
        //'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
        'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_rewardpoints'
      )); 
      
      $this->addColumn('gift', array(
        'header'    => Mage::helper('ordercustomer')->__('Gift Card'),
        'align'     =>'center',
        'index'     => 'gift',
        'filter'  => false,
        'sortable'  => false,  
        'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_giftcard'
      )); 
      
      $this->addColumn('total', array(
        'header'    => Mage::helper('ordercustomer')->__('Grand Total'),
        'align'     =>'center',
        'index'     => 'total',
        'filter'  => false,
        'sortable'  => false,  
        'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_total'
      )); 
      
     $this->addColumn('deposit', array(
        'header'    => Mage::helper('ordercustomer')->__('Cim Deposit'),
        'align'     =>'left',
        'type'  => 'price',
        'index'     => 'deposit',
        'filter_index'=>'sfc.deposit',  
        'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
      ));   
      
      $this->addColumn('payment_type', array(
          'header'    => Mage::helper('ordercustomer')->__('Payment Method'),
          'align'     =>'left',
          'width'     => '400px',
          'index'     => 'payment_type', 
          //'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_paymenttype'
      ));

      /*$this->addColumn('invoice_comment', array(
       		'header'    => Mage::helper('ordercustomer')->__('Invoice Comment'),
       		'align'     =>'left',
       		'width'     => '400px',
       		'filter'    => false,
          'index'     => 'invoice_comment'
       		//'renderer'  => 'ordercustomer/adminhtml_ordercustomer_grid_renderer_invoicecomment'
      ));*/
       
		  $this->addExportType('*/*/exportCsv', Mage::helper('ordercustomer')->__('CSV'));
		  $this->addExportType('*/*/exportXml', Mage::helper('ordercustomer')->__('XML'));
	  
      return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
        $this->setMassactionIdField('ordercustomer_id');
        $this->getMassactionBlock()->setFormFieldName('ordercustomer');

        $this->getMassactionBlock()->addItem('delete', array(
                'label'=> Mage::helper('catalog')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ordercustomer/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('catalog')->__('Change status'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                    'visibility' => array(
                            'name' => 'status',
                            'type' => 'select',
                            'class' => 'required-entry',
                            'label' => Mage::helper('catalog')->__('Status'),
                            'values' => $statuses
                    )
            )
        ));
        
        return $this;
    }

  public function getRowUrl($row)
  {
  }

}