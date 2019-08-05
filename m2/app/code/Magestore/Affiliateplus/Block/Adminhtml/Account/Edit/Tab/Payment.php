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
 * Class Payment
 * @package Magestore\Affiliateplus\Block\Adminhtml\Account\Edit\Tab
 */
class Payment extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Payment\CollectionFactory
     */
    protected $_paymentCollectionFactory;
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
        \Magestore\Affiliateplus\Model\ResourceModel\Payment\CollectionFactory $paymentCollectionFactory,
        array $data = array()
    )
    {
        parent::__construct($context, $backendHelper, $data);
        $this->_objectManager = $objectManager;
        $this->_paymentCollectionFactory = $paymentCollectionFactory;
        $this->_eventManager = $context->getEventManager();
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('paymentgrid');
        $this->setDefaultSort('payment_id');
        $this->setDefaultDir('ASC');
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
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column) {
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $collection = $this->_paymentCollectionFactory->create();

        $this->_eventManager->dispatch('affiliateplus_adminhtml_join_payment_other_table', ['collection' => $collection]);
        $collection->addFieldToFilter('account_id', $accountId);
        if($storeId = $this->getRequest()->getParam('store'))
            $collection->addFieldToFilter('store_ids', $storeId);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        $prefix = 'payment_grid_';
        $this->addColumn(
            $prefix . 'payment_id',
            [
                'header'           => __('ID'),
                'width'            => 60,
                'index'            => 'payment_id',
                'type'             => 'number',
            ]
        );

        $this->addColumn(
            $prefix . 'account_email',
            [
                'header'           => __('Account Email'),
                'index'            => 'account_email',
                'renderer'         => 'Magestore\Affiliateplus\Block\Adminhtml\Payment\Renderer\Account',
            ]
        );

        $this->addColumn(
            $prefix . 'amount',
            [
                'header'           => __('Amount'),
                'index'            => 'amount',
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
            ]
        );

        $this->addColumn(
            $prefix . 'fee',
            [
                'header'           => __('Fee'),
                'index'            => 'fee',
                'type'             => 'price',
                'currency_code'    => $store->getBaseCurrency()->getCode(),
            ]
        );

        $this->addColumn(
            $prefix . 'request_time',
            [
                'header'           => __('Date Requested'),
                'index'            => 'request_time',
                'align'            => 'right',
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_column_account_payment_grid', ['block' => $this]);

        $this->addColumn(
            $prefix . 'status',
            [
                'header'           => __('Status'),
                'index'            => 'status',
                'width'            => '80px',
                'align'            =>'right',
                'type'             => 'options',
                'options'   => [
                    1 => __('Pending'),
                    2 => __('Processing'),
                    3 => __('Complete'),
                    4 => __('Canceled')
                ],
            ]
        );

    }


    /**
     * @return mixed|string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            :  $this->getUrl('*/*/payment',
            [
                '_current' => TRUE,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            ]
        );
    }


    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('affiliateplusadmin/payment/edit', ['id' => $row->getId()]);
    }
}
