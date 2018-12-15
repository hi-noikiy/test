<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircustomer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('repaircustomerGrid');
      $this->setDefaultSort('repair_center_id');
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
      $collection = Mage::getModel('repaircenter/repaircustomer')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

      $this->addColumn('repair_center_id', array(
      'header'    => Mage::helper('repaircenter')->__('Repair No'),
      'width'     => '75px',
      'index'     => 'repair_center_id',
    ));
      
       $this->addColumn('customer', array(
	'header'    => Mage::helper('repaircenter')->__('Client Detail'),
	'index'     => 'customer',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_customer',   
    ));
	  
    $this->addColumn('product', array(
        'header'    => Mage::helper('repaircenter')->__('Product'),
        'align'     =>'left',
        'index'     => 'product',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_product',
    ));
      
    $this->addColumn('service_order_no', array(
      'header'    => Mage::helper('repaircenter')->__('Service center order No'),
      'width'     => '75px',
      'index'     => 'service_order_no',
      'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_service'  
    ));
     $this->addColumn('dispatch_date', array(
        'header' => Mage::helper('repaircenter')->__('Dispatch Date'),
        'index' => 'dispatch_date',
        'type' => 'datetime',
        'width' => '100px',
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_dispatch', 
    )); 
     
     $this->addColumn('diagnostic', array(
        'header'    => Mage::helper('repaircenter')->__('Diagnostic'),
        'align'     =>'center',
        'index'     => 'diagnostic',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_diagnostic',
    ));

    $this->addColumn('supplier_comments', array(
      'header'    => Mage::helper('repaircenter')->__('Supplier Comments'),
      'index'     => 'supplier_comments',
      'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_scomments'
    ));
    
    $this->addColumn('warranty_status', array(
        'header'    => Mage::helper('repaircenter')->__('Warranty Status'),
        'align'     =>'center',
        'index'     => 'warranty_status',
        'type'      => 'options',
        'options'   => array(
              1 => 'Warranty Ok',
              2 => 'Warranty Void',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_wstatus',
    ));
    $this->addColumn('client_informed', array(
        'header'    => Mage::helper('repaircenter')->__('Client Informed'),
        'align'     =>'center',
        'index'     => 'client_informed',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_clientinform',
    ));
    
    $this->addColumn('comments', array(
      'header'    => Mage::helper('repaircenter')->__('Comments'),
      'index'     => 'comments',
      'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_comments'
    ));
    
    $this->addColumn('supplier_informed', array(
        'header'    => Mage::helper('repaircenter')->__('Supplier Informed'),
        'align'     =>'center',
        'index'     => 'supplier_informed',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_supplierinform',
    ));
    $this->addColumn('replacement', array(
        'header'    => Mage::helper('repaircenter')->__('Replacement Device'),
        'align'     =>'center',
        'index'     => 'replacement',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_replacement',
    ));
     
     $this->addColumn('collect_date', array(
        'header' => Mage::helper('repaircenter')->__('Collect Date'),
        'index' => 'collect_date',
        'type' => 'datetime',
        'width' => '100px',
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_collect', 
    )); 
     
    $this->addColumn('leadtime', array(
        'header'    => Mage::helper('repaircenter')->__('Lead Time'),
        'align'     =>'left',
        'index'     => 'leadtime',
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_leadtime',
    ));
    
     $this->addColumn('is_pickup', array(
        'header'    => Mage::helper('repaircenter')->__('Product Collect'),
        'align'     =>'center',
        'index'     => 'is_pickup',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_pickup',
    ));
     
     $this->addColumn('status', array(
        'header' => Mage::helper('repaircenter')->__('Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
       // 'filter_index'=>'main_table.status',
        'options'   => array(
              1 => 'Pending',
              2 => 'Complete',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_status',
    ));
     
   
    $this->addColumn('button', array(
        'header' => Mage::helper('repaircenter')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'repair_customer_id',
        'renderer'  => 'repaircenter/adminhtml_repaircustomer_grid_renderer_button'
    ));

  $this->addExportType('*/*/exportCsv', Mage::helper('repaircenter')->__('CSV'));
	$this->addExportType('*/*/exportXml', Mage::helper('repaircenter')->__('XML'));
	  
      return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
  
  }

  public function getRowUrl($row)
  {
    return false;
  }

  public function getGridUrl()
  {
        return $this->getUrl('*/*/grid', array('_current'=>true));
  }

}