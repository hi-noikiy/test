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
namespace Magestore\Affiliateplus\Block\Adminhtml\Account\Edit\Tab;

/**
 * Grid Grid
 */
class Transaction extends \Magento\Backend\Block\Widget\Grid\Extended
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
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('transactiongrid');
        $this->setDefaultSort('transaction_id');
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
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column) {
        return parent::_addColumnFilterToCollection($column);
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $collection = $this->_transactionCollectionFactory->create();

        $this->_eventManager->dispatch('affiliateplus_adminhtml_join_transaction_other_table', ['collection' => $collection]);

        $collection->addFieldToFilter('account_id', $accountId);

        if($storeId = $this->getRequest()->getParam('store'))
            $collection->addFieldToFilter('store_id', $storeId);
        $collection->setStoreViewId($storeId);

        $collection->getSelect()
            ->columns(
                [
                    'customer_email' => 'main_table.customer_email'
                ]
            );
        $collection->getSelect()->columns(
                [
                    'order_number' => 'main_table.order_number'
                ]
            );
        $collection->getSelect()->columns(
               [
                    'order_item_names' => 'main_table.order_item_names'
                ]
            );
        $this->_eventManager->dispatch('affiliateplus_adminhtml_after_set_transaction_collection', ['grid' => $this, 'account_id' => $accountId, 'store' => $storeId]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $prefix = 'transaction_grid_';
        $this->addColumn(
            $prefix . 'transaction_id',
            [
                'header'           => __('ID'),
                'width'            => 60,
                'index'            => 'transaction_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'filter_index' => 'main_table.transaction_id',
            ]
        );

        $this->addColumn(
            $prefix . 'customer_email',
            [
                'header'           => __('Customer Email'),
                'width' => '150px',
                'align' => 'right',
                'index'            => 'customer_email',
                'filter_index' => 'main_table.customer_email',
                'renderer'         => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Customer',
            ]
        );

        $this->addColumn(
            $prefix . 'order_number',
            [
                'header'           => __('Order'),
                'width' => '150px',
                'align' => 'right',
                'index'            => 'order_number',
                'filter_index' => 'main_table.order_number',
                'renderer'  => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Order',
            ]
        );

        $this->addColumn(
            $prefix . 'order_item_names',
            [
                'header'           => __('Product Name'),
                'align'     =>'left',
                'index'            => 'order_item_names',
                'filter_index' => 'main_table.order_item_names',
                'renderer'  => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Product',
            ]
        );

        $this->addColumn(
            $prefix . 'total_amount',
            [
                'header'           => __('Order Subtotal'),
                'width'     => '150px',
                'align'     =>'left',
                'index'            => 'total_amount',
                'type' => 'price',
                'currency_code' => $currencyCode,
            ]
        );

        $this->addColumn(
            $prefix . 'commission',
            [
                'header'           => __('Commission'),
                'index'            => 'commission',
                'width'            => '150px',
                'align'            =>'right',
                'type'             => 'price',
                'currency_code'    => $currencyCode,
            ]
        );

        $this->addColumn(
            $prefix . 'discount',
            [
                'header'           => __('Affiliate Discount'),
                'index'            => 'discount',
                'width'            => '150px',
                'align'            =>'right',
                'type'             => 'price',
                'currency_code'    => $currencyCode,
            ]
        );


        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_column_account_transaction_grid', ['grid' => $this]);

        $this->addColumn(
            $prefix . 'created_time',
            [
                'header'           => __('Date Created'),
                'index'            => 'created_time',
                'width'            => '150px',
                'align'            =>'right',
//                'type'             => 'date',
            ]
        );


        $this->addColumn(
            $prefix . 'status',
            [
                'header'  => __('Order Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => [
                    1 => __('Complete'),
                    2 => __('Pending'),
                    3 => __('Canceled'),
                    4 => __('On Hold'),
                ],
            ]
        );

    }
    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url') ? $this->getData('grid_url') :$this->getUrl('*/account/transaction',
            [
            '_current' => true,
             'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
             ]
        );
    }
    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('affiliateplusadmin/transaction/view', ['transaction_id' => $row->getId()]);
    }

}
