<?php

namespace Ktpl\Guestabandoned\Block\Adminhtml\Guestabandoned;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    protected $_quotesFactory;
    protected $_scopeConfig;
    protected $_status;

    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quotesFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ktpl\Guestabandoned\Model\Status $status
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quotesFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Ktpl\Guestabandoned\Model\Status $status, array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_status = $status;
        $this->_quotesFactory = $quotesFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct() {
        parent::_construct();
        $this->setId('guestabandonedGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $fromDate = $this->_scopeConfig->getValue('customer/guestabandoned/startdate');
        $collection = $this->_quotesFactory->create();
        $collection->addFieldToFilter('main_table.customer_id', array('null' => true))
                ->addFieldToFilter('main_table.created_at', array('from' => $fromDate, true))
                ->addFieldToFilter('main_table.is_active', array('eq' => '1'));
        $collection->getSelect()->joinleft('quote_address', 

                'main_table.entity_id = quote_address.quote_id AND quote_address.address_type = "shipping"', 
                array('telephone'));
       
        $collection->addFieldToFilter('quote_address.telephone', array('notnull' => true));
        //echo $collection->getSelect();die;
        $this->setCollection($collection);
        //return $collection;
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('quote_id', array(
            'header' => __('ID'),
            'width' => '75px',
            'index' => 'entity_id',
        ));

        $this->addColumn('store_id', array(
            'header' => __('Purchased From (Store)'),
            'width' => '100px',
            'index' => 'store_id',
            'type' => 'store',
            'store_view' => true,
        ));

        $this->addColumn('customer_firstname', array(
            'header' => __('First Name'),
            'width' => '75px',
            'index' => 'customer_firstname',
        ));

        $this->addColumn('customer_lastname', array(
            'header' => __('Last Name'),
            'width' => '75px',
            'index' => 'customer_lastname',
        ));

        $this->addColumn('customer_email', array(
            'header' => __('Email'),
            'width' => '100px',
            'index' => 'customer_email',
        ));
        $this->addColumn('telephone', array(
            'header' => __('Telephone'),
            'width' => '100px',
            'index' => 'telephone',
        ));

        $this->addColumn('created_at', array(
            'header' => __('Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
            'filter_index'=>'quote_address.created_at'
        ));

        $statuses = $this->_status->getOptionArray();
        $this->addColumn('status', array(
            'header' => __('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => $statuses,
        ));

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View'),
                    'url' => array('base' => '*/*/view'),
                    'field' => 'entity_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/ExportCsv', __('CSV'));
        //$this->addExportType('*/*/ExportXml', __('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?')
        ));

        $statuses = $this->_status->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => __('Change status'),
            'url' => $this->getUrl('*/*/MassStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Status'),
                    'values' => $statuses
                )
            )
        ));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/view', array('_current' => true, 'entity_id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

}
