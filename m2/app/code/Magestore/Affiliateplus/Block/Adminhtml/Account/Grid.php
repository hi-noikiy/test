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
namespace Magestore\Affiliateplus\Block\Adminhtml\Account;

/**
 * Grid Grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory
     */
    protected $_accountCollectionFactory;
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
        \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        array $data = array()
    )
    {
        parent::__construct($context, $backendHelper, $data);
        $this->_objectManager = $objectManager;
        $this->_accountCollectionFactory = $accountCollectionFactory;
        $this->_eventManager = $context->getEventManager();
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('AccountGrid');
        $this->setDefaultSort('account_id');
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

        $collection = $this->_accountCollectionFactory->create();
        $this->_eventManager->dispatch('affiliateplus_adminhtml_join_account_other_table', ['collection' => $collection]);
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
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $this->addColumn(
            'account_id',
            [
                'header'           => __('ID'),
                'index'            => 'account_id',
                'type'             => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'filter_index'	=> 'main_table.account_id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header'           => __('Name'),
                'index'            => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
                'filter_index'	=> 'main_table.name'
            ]
        );

        $this->addColumn(
            'email',
            [
                'header'           => __('Email Address'),
                'index'            => 'email',
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email',
                'filter_index'	=> 'main_table.email'
            ]
        );

        $this->addColumn(
            'balance',
            [
                'header'           => __('Balance'),
//                'index'            => 'balance',
                'header_css_class' => 'col-balance',
                'column_css_class' => 'col-balance',
                'type'             => 'price',
                'currency_code'    => $currencyCode,
                'filter_index'	=> 'main_table.balance',
                'renderer'          => 'Magestore\Affiliateplus\Block\Adminhtml\Account\Renderer\Balance',
            ]
        );

        $this->addColumn(
            'total_commission_received',
            [
                'header'           => __('Commission Paid'),
                'index'            => 'total_commission_received',
                'header_css_class' => 'col-total_commission_received',
                'column_css_class' => 'col-total_commission_received',
                'type'             => 'price',
                'currency_code'    => $currencyCode,
                'filter_index'	   => 'main_table.total_commission_received'
            ]
        );

        $this->addColumn(
            'total_paid',
            [
                'header'           => __('Total Paid'),
                'index'            => 'total_paid',
                'header_css_class' => 'col-total_paid',
                'column_css_class' => 'col-total_paid',
                'type'             => 'price',
                'currency_code'    => $currencyCode,
                'filter_index'	=> 'main_table.total_paid',
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_adminhtml_add_column_account_grid', ['grid' => $this]);
        $this->addColumn(
            'status',
            [
                'header'  => __('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => \Magestore\Affiliateplus\Model\Status::getAvailableStatuses(),
                'filter_index'	=> 'main_table.status',
            ]
        );

        $this->addColumn(
            'approved',
            [
                'header'           => __('Approved'),
                'index'            => 'approved',
                'header_css_class' => 'col-total_paid',
                'column_css_class' => 'col-total_paid',
                'filter_index'	=> 'main_table.approved',
                'type'             => 'options',
                'options'   => array(
                    1 => 'Yes',
                    2 => 'No',
                ),
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
                    'field'   => 'account_id',
                    ],
                ],
                'filter'           => FALSE,
                'sortable'         => FALSE,
                'index'            => 'entity_id',
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
        $storeId = $this->getRequest()->getParam('store');
        $this->setMassactionIdField('account_id');
        $this->getMassactionBlock()->setFormFieldName('account');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'   => __('Delete'),
                'url'     => $this->getUrl('affiliateplusadmin/*/massDelete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        $statuses = \Magestore\Affiliateplus\Model\Status::getAvailableStatuses();

        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label'      => __('Change status'),
                'url'        => $this->getUrl('affiliateplusadmin/*/massStatus', ['_current' => true,'store'=>$storeId]),
                'additional' => [
                    'visibility' => [
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => __('Status'),
                        'values' => $statuses,
                    ],
                ],
            ]
        );

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
        return $this->getUrl('*/*/edit', ['account_id' => $row->getId(),'store' => $this->getRequest()->getParam('store')]);
    }
}
