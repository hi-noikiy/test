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
namespace Magestore\Affiliateplus\Block\Adminhtml\Payment;

/**
 * Grid Grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Account helper payment
     *
     * @var \Magestore\Account\Helper\Payment
     */
    protected $_helperPayment;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magestore\Affiliateplus\Helper\Payment $helperPayment
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Affiliateplus\Helper\Payment $helperPayment,
        array $data = array()
    )
    {
        parent::__construct($context, $backendHelper, $data);
        $this->_eventManager = $context->getEventManager();
        $this->_objectManager = $objectManager;
        $this->_helperPayment = $helperPayment;
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('Grid');
        $this->setDefaultSort('payment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Payment\Collection');

        $this->_eventManager->dispatch('affiliateplus_adminhtml_join_payment_other_table', array('collection' => $collection));

        $storeId = $this->getRequest()->getParam('store');
        if($storeId)
            $collection->addFieldToFilter('store_ids', array('finset' => $storeId));

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
            'payment_id',
            [
                'header'            => __('ID'),
                'index'             => 'payment_id',
                'type'              => 'number',
                'header_css_class'  => 'col-id',
                'column_css_class'  => 'col-id',
                'width'             => '50px',
            ]
        );

        $this->addColumn(
            'account_email',
            [
                'header'            => __('Affiliate Email'),
                'index'             => 'account_email',
                'header_css_class'  => 'col-name',
                'column_css_class'   => 'col-name',
                'width'             => '150px',
                'renderer'          => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Account',
            ]
        );

        $this->addColumn(
            'amount',
            [
                'header'            => __('Amount'),
                'index'             => 'amount',
                'align'             =>'right',
                'width'             => '90px',
                'header_css_class'  => 'col-price',
                'column_css_class'  => 'col-price',
                'type'              => 'price',
                'currency_code'     => $store->getBaseCurrency()->getCode(),
            ]
        );

        $this->addColumn(
            'tax_amount',
            [
                'header'            => __('Tax Amount'),
                'index'             => 'tax_amount',
                'align'             =>'right',
                'width'             => '90px',
                'header_css_class'  => 'col-price',
                'column_css_class'  => 'col-price',
                'type'              => 'price',
                'currency_code'     => $store->getBaseCurrency()->getCode(),
            ]
        );

        $this->addColumn(
            'fee',
            [
                'header'            => __('Fee'),
                'index'             => 'fee',
                'align'             =>'right',
                'width'             => '90px',
                'header_css_class'  => 'col-price',
                'column_css_class'  => 'col-price',
                'type'              => 'price',
                'currency_code'     => $store->getBaseCurrency()->getCode(),
            ]
        );

        $this->addColumn(
            'payment_method',
            [
                'header'  => __('Withdrawal Method'),
                'index'   => 'payment_method',
                'type'    => 'options',
                'width'   => '150px',
                'options' => $this->_helperPayment->getAllPaymentOptionArray(),
                'renderer'  => 'Magestore\Affiliateplus\Block\Adminhtml\Payment\Renderer\Info',
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_adminhtml_change_column_payment_grid', ['grid' => $this]);

        $this->addColumn(
            'request_time',
            [
                'header'  => __('Date Requested'),
                'index'   => 'request_time',
                'type'    => 'date',
                'width'   => '150px',
                'align'     =>'right',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header'  => __('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'width'   => '80px',
                'align'     =>'left',
                'options' => \Magestore\Affiliateplus\Model\Payment::getPaymentStatus(),
            ]
        );



        $this->addColumn(
            'edit',
            [
                'header'           => __('Action'),
                'type'             => 'action',
                'getter'           => 'getId',
                'actions'          => [
                    [
                    'caption' => __('Edit'),
                    'url'     => ['base' => '*/*/edit'],
                    'field'   => 'payment_id',
                    ],
                ],
                'filter'           => FALSE,
                'sortable'         => FALSE,
                'index'            => 'payment_id',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
                'is_system' => true,
            ]
        );

//        $this->addExportType('*/*/exportCsv', __('CSV'));
//        $this->addExportType('*/*/exportXml', __('XML'));
//        $this->addExportType('*/*/exportExcel', __('Excel'));

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareMassaction()
    {
        // add more mass action for payment grid
        $this->_eventManager->dispatch('affiliateplus_adminhtml_payment_massaction', array('grid' => $this));

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
        return $this->getUrl('*/*/edit', ['payment_id' => $row->getId()]);
    }
}
