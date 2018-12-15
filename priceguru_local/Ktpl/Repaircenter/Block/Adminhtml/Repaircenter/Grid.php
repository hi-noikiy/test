<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('repaircenterGrid');
      $this->setDefaultSort('repair_id');
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
      $collection = Mage::getModel('repaircenter/repaircenter')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

      $this->addColumn('repair_id', array(
      'header'    => Mage::helper('repaircenter')->__('Repair No'),
      'width'     => '75px',
      'index'     => 'repair_id',
    ));
      
     $this->addColumn('created_time', array(
        'header' => Mage::helper('repaircenter')->__('Date'),
        'index' => 'created_time',
        'type' => 'datetime',
        'width' => '100px',
    )); 
     
    $this->addColumn('increment_id', array(
      'header'    => Mage::helper('repaircenter')->__('Order #'),
      'width'     => '75px',
      'index'     => 'increment_id',
    ));

  

    $this->addColumn('customer', array(
	'header'    => Mage::helper('repaircenter')->__('Client Detail'),
	'index'     => 'customer',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_customer'
    ));
    
    $this->addColumn('c_address', array(
	'header'    => Mage::helper('repaircenter')->__('Client address'),
	'index'     => 'c_address',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_caddress'
    ));
    $this->addColumn('c_latitude', array(
	'header'    => Mage::helper('repaircenter')->__('Latitude'),
	'index'     => 'c_latitude',
        'filter'    => false,
        'sortable'  => false,
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_clatitude'
    ));
    $this->addColumn('c_longitude', array(
	'header'    => Mage::helper('repaircenter')->__('Longitude'),
	'index'     => 'c_longitude',
        'filter'    => false,
        'sortable'  => false,
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_clongitude'
    ));
	  
    $this->addColumn('product', array(
        'header'    => Mage::helper('repaircenter')->__('Product'),
        'align'     =>'left',
        'index'     => 'product',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_product'
    ));
    
    $this->addColumn('serial_no', array(
      'header'    => Mage::helper('repaircenter')->__('Serial No'),
      'index'     => 'serial_no',
      'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_serialno'
    ));
    
    $this->addColumn('problem_description', array(
      'header'    => Mage::helper('repaircenter')->__('Problem Description'),
      'index'     => 'problem_description',
      'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_problem'
    ));
    
     $this->addColumn('wholesaler', array(
      'header'    => Mage::helper('repaircenter')->__('Wholesaler'),
      'width'     => '75px',
      'index'     => 'wholesaler',
      'type'  => 'options',   
      'options'   => $this->getWholesaler(),   
     // 'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_wholesaler'   
    ));
    
    $this->addColumn('sc_id', array(
      'header'    => Mage::helper('repaircenter')->__('Service Center'),
      'align'     =>'left',
      'index'     => 'sc_id',
      'type'      => 'options',
      'options' => $this->getServicecenter(),
      'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_scenter',
    )); 

    $this->addColumn('sc_address', array(
        'header'    => Mage::helper('repaircenter')->__('Service Center Address'),
        'index'     => 'sc_address',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_serviceadd',
    ));
    
    $this->addColumn('sc_latitude', array(
        'header'    => Mage::helper('repaircenter')->__('Latitude'),
        'index'     => 'sc_latitude',
        'width'     => '100px',       
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_lati',
    )); 
    $this->addColumn('sc_longitude', array(
        'header'    => Mage::helper('repaircenter')->__('Longitude'),
        'index'     => 'sc_longitude',
        'width'     => '100px',       
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_longi',
    )); 
    
    $this->addColumn('pickup_option', array(
        'header' => Mage::helper('repaircenter')->__('Pickup Option'),
        'index' => 'pickup_option',
        'type'  => 'options',
        'options'   => array(
              1 => 'Pickup Customer',
              2 => 'Customer Drop',
              3 => 'SAV Home',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_pickupoption'
    ));
    
    $this->addColumn('pickup_address', array(
        'header'    => Mage::helper('repaircenter')->__('Pickup Address'),
        'align'     =>'left',
        'width'     => '100px',            
        'index'     => 'pickup_address',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_pickupaddress',
        
    ));

    $this->addColumn('is_pickup', array(
        'header'    => Mage::helper('repaircenter')->__('Pickup'),
        'align'     =>'center',
        'index'     => 'is_pickup',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_pickup',
    ));
    
    $this->addColumn('pickup_date', array(
        'header' => Mage::helper('repaircenter')->__('Product Inhouse Date'),
        'index' => 'pickup_date',
        'type' => 'datetime',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_pickupdate',
    )); 
    
    $this->addColumn('dispatch', array(
        'header'    => Mage::helper('repaircenter')->__('Dispatch'),
        'align'     =>'center',
        'index'     => 'dispatch',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_dispatch',
    ));
    $this->addColumn('dispatch_date', array(
        'header' => Mage::helper('repaircenter')->__('Dispatch Date'),
        'index' => 'dispatch_date',
        'type' => 'datetime',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_dispatchdate',
    )); 

    $this->addColumn('status', array(
        'header' => Mage::helper('repaircenter')->__('Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
        'filter_index'=>'main_table.status',
        'options'   => array(
              1 => 'Pending',
              2 => 'Complete',
              3 => 'SAV Home'
          ),
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_status',
    ));
    
    
    $this->addColumn('button', array(
        'header' => Mage::helper('repaircenter')->__('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'repair_id',
        'renderer'  => 'repaircenter/adminhtml_repaircenter_grid_renderer_button'
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
  public function getWholesaler(){
      $dataarr= array();
      $wholesalers = Mage::getSingleton('customreport/wholesaler')->getCollection();
      foreach ($wholesalers as $k=> $wholesaler) {
          $dataarr[$wholesaler->getId()] = $wholesaler->getName();
          
      }
      return $dataarr;
  }
  public function getServicecenter()
    {
    	$wArray = array();
    	$wdata = Mage::getSingleton('repaircenter/servicecenter')->getCollection()->addFieldToFilter('service_status',1);
    	foreach($wdata as $item) {
    		$wArray[$item->getServiceId()] = $item->getServiceName();
    	}
    	return $wArray;
    }

}