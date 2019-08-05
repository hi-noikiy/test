<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Adminhtml\Transaction;

/**
 * Grid Grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $_transactionCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Affiliateplus\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        array $data = array()
    )
    {
        parent::__construct($context, $backendHelper, $data);
        $this->_objectManager = $objectManager;
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
        $this->_eventManager = $context->getEventManager();
        $this->_storeManager = $context->getStoreManager();
        $this->_objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {

        $collection = $this->_transactionCollectionFactory->create();
        $this->_eventManager->dispatch('affiliateplus_adminhtml_join_transaction_other_table', ['collection' => $collection]);
        $storeId = $this->getRequest()->getParam('store');
        $collection->setStoreViewId($storeId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        $this->addColumn(
            'transaction_id',
            [
                'header'           => __('ID'),
                'index'            => 'transaction_id',
                'type'             => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'order_item_names',
            [
                'header'           => __('Product Name'),
                'index'            => 'order_item_names',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
                'width'     => '150px',
//                'filter_index'  =>  'if (main_table.order_item_names IS NULL, "N/A", main_table.order_item_names)',
                'renderer'  => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Product',
                'filter_condition_callback' => [$this, '_filterProductName'],
            ]
        );

        $this->addColumn(
            'account_email',
            [
                'header'           => __('Affiliate Email'),
                'width'     => '150px',
                'index'            => 'account_email',
                'renderer'  => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Account',
            ]
        );

        $this->addColumn(
            'customer_email',
            [
                'header'           => __('Customer Email'),
                'width'     => '150px',
                'align'     =>'left',
                'index'            => 'customer_email',
//                'filter_index'  =>  'if (main_table.customer_email="", "NA", main_table.customer_email)',
                'renderer'  => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Customer',
                'filter_condition_callback' => [$this, '_filterCustomerEmail'],
            ]
        );

        $this->addColumn(
            'order_number',
            [
                'header'           => __('Order ID'),
                'width'     => '150px',
                'align'     =>'left',
                'index'            => 'order_number',
//                'filter_index'  =>  'if (main_table.order_number="", "N/A", main_table.order_number)',
                'renderer'  => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Order',
                'filter_condition_callback' => [$this, '_filterOrderId'],
            ]
        );

        $this->addColumn(
            'total_amount',
            [
                'header'           => __('Order Subtotal'),
                'index'            => 'total_amount',
                'align'     =>'right',
                'width'     => '150px',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
                'type'      => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ]
        );

        $this->addColumn(
            'commission',
            [
                'header'           => __('Commission'),
                'index'            => 'commission',
                'align'     =>'right',
                'width'     => '150px',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
                'type'      => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ]
        );

        $this->addColumn(
            'discount',
            [
                'header'           => __('Discount'),
                'index'            => 'discount',
                'align'     =>'right',
                'width'     => '150px',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
                'type'      => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_column_transaction_grid', array('grid' => $this));

        $this->addColumn(
            'created_time',
            [
                'header'           => __('Date Created'),
                'index'            => 'created_time',
                'align'     =>'right',
                'width'     => '150px',
                'type'      => 'datetime',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header'  => __('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'width'   => '80px',
                'options' => \Magestore\Affiliateplus\Model\Transaction::getTransactionStatus(),
            ]
        );

        $this->addColumn('store_id', array(
            'header'    => __('Store view'),
            'align'     =>'left',
            'index'     =>'store_id',
            'type'      =>'store',
            'store_view'=>true,
        ));

        $this->addColumn(
            'edit',
            [
                'header'           => __('Action'),
                'type'             => 'action',
                'getter'           => 'getId',
                'actions'          => [
                    [
                    'caption' => __('View'),
                    'url'     => ['base' => '*/*/view'],
                    'field'   => 'transaction_id',
                    ],
                ],
                'filter'           => FALSE,
                'sortable'         => FALSE,
                'index'            => 'transaction_id',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        $this->addExportType('*/*/exportExcel', __('Excel'));

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('transaction_id');
        $this->getMassactionBlock()->setFormFieldName('transaction');

//        $this->getMassactionBlock()->addItem(
//            'cancel',
//            [
//                'label'   => __('Cancel'),
//                'url'     => $this->getUrl('*/*/massCancel'),
//                'confirm' => __('This action cannot be restored. Are you sure?'),
//            ]
//        );

        $this->getMassactionBlock()->addItem(
            'complete',
            [
                'label'     => __('Unhold Transactions'),
                'url'       => $this->getUrl('*/*/massStatus'),
                'confirm'   => __('This action cannot be restored. Are you sure?'),
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_massaction_transaction_grid', array('grid' => $this));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => TRUE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', ['transaction_id' => $row->getId()]);
    }

    /**
     * @param $collection
     * @param $column
     */
    protected  function _filterProductName($collection, $column) {
        $value = $column->getFilter()->getValue();
        $collection->getSelect()->where("if (main_table.order_item_names IS NULL, 'N/A', main_table.order_item_names) like '%$value%'");
    }

    /**
     * @param $collection
     * @param $column
     */
    protected  function _filterCustomerEmail($collection, $column) {
        $value = $column->getFilter()->getValue();
        $collection->getSelect()->where("if (main_table.customer_email='', 'NA' , main_table.customer_email) like '%$value%'");
    }

    /**
     * @param $collection
     * @param $column
     */
    protected  function _filterOrderId($collection, $column) {
        $value = $column->getFilter()->getValue();
        $collection->getSelect()->where("if (main_table.order_number='', 'N/A', main_table.order_number) like '%$value%'");
    }
}
