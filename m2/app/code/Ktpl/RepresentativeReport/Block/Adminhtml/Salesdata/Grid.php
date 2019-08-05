<?php

namespace Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    protected $_coreSession;
    protected $_datetime;
    protected $_helper;

    /**
     * 
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Ktpl\RepresentativeReport\Helper\Data $helper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,    
        \Ktpl\RepresentativeReport\Helper\Data $helper,    
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,    
        array $data = []    
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_coreSession = $coreSession;
        $this->_datetime = $datetime;
        $this->_helper = $helper;
        
    }

    public function _construct() {
        parent::_construct();
        $this->setId('salesdata');
        $this->setDefaultSort('sku');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * 
     * @return collection
     */
    public function _prepareCollection() {
        $collection = $this->getitems();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     * @return collection
     */
    public function getitems() {
        $data = $this->_coreSession->getMyCustomData();
        $from_date = $this->_datetime->gmtDate(null, strtotime($data['start_date'] . ' 00:00:00'));
        $to_date = $this->_datetime->gmtDate(null, strtotime($data['end_date'] . ' 23:59:59'));
        
        $collect = $this->_helper->getcollect();
        $collect->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collect->getSelect()->columns(array('SUM(qty_ordered) as order_quantity', 'sku','item_id', 'product_id', 'name', 'price', '(SUM(qty_ordered) * price) as revenue'))
                ->where(sprintf('salesord.created_at > "%s" AND salesord.created_at < "%s"', date('Y-m-d H:i:s', strtotime($from_date)), date('Y-m-d H:i:s', strtotime($to_date))))
                ->group('sku');
        
        return $collect;
    }
    
    /**
     * 
     * @return $this
     */
    protected function _prepareColumns() {
        
        $this->addColumn('sku', array(
            'header' => __('Sku'),
            'index' => 'sku',
            'type' => 'text',
           )
        );

        $this->addColumn('name', array(
            'header' => __('Product Name'),
            'index' => 'name',
            'type' => 'text',
           )
        );

        $this->addColumn('order_quantity', array(
            'header' => __('Quantity'),
            'index' => 'order_quantity',
            'type' => 'number',
            'default' => '0',
            //'filter' =>FALSE,
            'filter_condition_callback' => array($this, 'filter_orderqty'),
           )
        );
        
        $this->addColumn('price', array(
            'header' => __('Price'),
            'index' => 'price',
            'type' => 'number',
           )
        );

        $this->addColumn('revenue', array(
            'header' => __('Revenue'),
            'index' => 'revenue',
            'type' => 'number',
            'filter' =>FALSE,
            //'filter_condition_callback' => array($this, 'filter_revenue'),
            'default' => '0',
           )
        );

        $this->addColumn('product_id', array(
            'header' => __('Inclusion Rate'),
            'index' => 'product_id',
            'type' => 'number',
            'filter' => FALSE,
            'sortable' => FALSE,
            'renderer' => \Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Renderer\Inclusion::class,
            'default' => '0',
           )
        );
        
        $this->addColumn('item_id', array(
            'header' => __('Position'),
            'index' => 'item_id',
            'type' => 'number',
            'filter' =>FALSE,
            'sortable' => FALSE,
            'renderer' => \Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Renderer\Position::class,
            'default' => '0',
          //  'filter_condition_callback' => array($this, '_customShippingFilterCallBack')
           )
        );

        $this->addExportType('*/*/ExportOrderedCsv', __('CSV'));
        $this->addExportType('*/*/ExportOrderedXml', __('Excel'));

        return $this;
    }
    
   /**
    * 
    * @param type $collection
    * @param type $column
    * @return $this
    */
    public function filter_orderqty($collection, $column){
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        
        if(@$value['from']){
            $this->getCollection()->getSelect()->having("SUM(qty_ordered) >= ?", $value['from']);
        }
        if(@$value['to']){
            $this->getCollection()->getSelect()->having('SUM(qty_ordered) <=  ?', $value['to']);
        }//echo $this->getCollection()->getSelect();exit;
        
        return $this;
    }
    
    /**
     * 
     * @return url
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
    
}
