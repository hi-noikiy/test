<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder;
 
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    protected $moduleManager;


    protected $_pickuporderFactory;

    protected $_status;


    public function __construct( 
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ktpl\Customreport\Model\ResourceModel\Pickuporder\CollectionFactory $pickuporderFactory,
         \Ktpl\Customreport\Model\Renderer\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) { 
     
        $this->_pickuporderFactory = $pickuporderFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        
        parent::_construct();
        $this->setId('pickuporderGrid');
        $this->setDefaultSort('order_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
       // $this->setDefaultFilter(array('pickup' => 0));
        $this->setUseAjax(true);
        $this->setVarNameFilter('lists_filter');
        
       
        
        
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    { 
        $collection = $this->_pickuporderFactory->create();
        $this->setCollection($collection);
       
        return  parent::_prepareCollection();
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {

      //  echo '111asdgsga'; exit;
        $this->addColumn(
            'order_id',
            [
                'header' => __('Order #'),
                'type' => 'number',
                'index' => 'order_id',
                
            ]
        );
        $this->addColumn('order_created_date', array(
        'header' => __('Invoice On'),
        'index' => 'order_created_date',
        'type' => 'datetime',
                    
    ));
         $this->addColumn('customer_name', array(
        'header'    => __('Customer name'),
        'align'     =>'left',
        'width'     => '400px',            
        'index'     => 'customer_name',
        'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Customername',
    ));

    $this->addColumn('product_name', array(
        'header'    => __('Product name'),
        'align'     =>'left',
     		'width'     => '400px',       		 
        'index'     => 'product_name',
    ));

    $this->addColumn('sku', array(
          'header'    => __('SKU'),
          'align'     =>'left',
          'index'     => 'sku',
    ));

    $this->addColumn('qty', array(
        'header'    => __('Qty'),
        'align'     =>'center',
        'index'     => 'qty',
    ));

    $this->addColumn('attributes', array(
        'header'    => __('Attributes'),
        'align'     =>'left',
        'index'     => 'attributes',
        'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Attributes',
    ));

   
    $this->addColumn('wholesale_price', array(
        'header'    => __('Wholesale Price'),
        'align'     =>'left',
        'index'     => 'wholesale_price',
        'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Wholesaleprice',
    ));

    $this->addColumn('retail_price', array(
        'header'    => __('Retail Price'),
        'align'     =>'left',
        'index'     => 'retail_price',
        'type'  => 'currency',
        'currency_code' => 'MUR',
    ));
      
    $this->addColumn('markup', array(
     		'header'    => __('Markup'),
     		'align'     =>'center',
        'index'     => 'markup',
        'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Markup',
    ));
      
    $this->addColumn('wholesaler_id', array(
      'header'    => __('Wholesaler'),
      'align'     =>'left',
      'index'     => 'wholesaler_id',
      'type'      => 'options',
      'options' => $this->allWholesaler(),
      'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Wholesaler',
    ));

    $this->addColumn('pickup_address', array(
        'header'    => __('Wholesaler Address'),
        'align'     =>'left',
        'index'     => 'pickup_address',
        'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Pickupaddress',
    ));

    $this->addColumn('payment_method', array(
        'header'    => __('Payment Method'),
        'align'     =>'left',
        'index'     => 'payment_method',
    ));

    $this->addColumn('pickup', array(
        'header'    => __('Pickup'),
        'align'     =>'center',
        'index'     => 'pickup',
        'type'      => 'options',
        'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
        'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Pickup',
    ));
  

    $this->addColumn('status', array(
        'header' => __('Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
        'options'   => array(
              1 => 'Pending',
              2 => 'Cancel',
              3 => 'Complete',
              4 => 'On Hold'
          ),
        'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Status',
    ));


    $this->addColumn('button', array(
        'header' => __('Update'),
        'width' => '50px',
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'pickup_id',
        'renderer'  => '\Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer\Button'
    ));

    $this->addColumn('action',
        array(
            'header'    => __('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'     => 'getRealOrderId',
            'actions'   => array(
                array(
                    'caption' => __('View'),
                    'url'     => array('base'=>'sales/order/view'),
                    'field'   => 'order_id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'is_system' => true,
    ));
        
             $this->addExportType('*/*/exportCsv', __('CSV'));
        return parent::_prepareColumns();    
    }

  
   
    public function getGridUrl() {
        return $this->getUrl('*/pickuporder/grid', array('_current' => true));
    }
    
    public function allWholesaler()
    {
       //$html = array(0 => '');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $wholesalers = $objectManager->create('Ktpl\Customreport\Model\Wholesaler')->getCollection();
    	
        foreach ($wholesalers as $wholesaler) {
        
            $html[$wholesaler->getId()] = $wholesaler->getName();
        }
       
        return $html;
    }
    /**
     * @param \Magento\Framework\Object $row
     * @return string
     *
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'pickuporder//edit',
            ['id' => $row->getId()]
        );
    } */
}  