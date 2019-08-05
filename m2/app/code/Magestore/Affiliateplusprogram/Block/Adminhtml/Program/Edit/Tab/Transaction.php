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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Transaction
 * @package Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab
 */
class Transaction extends \Magento\Backend\Block\Widget\Grid\Extended implements TabInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Account constructor.
     * @param Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->_backendSession = $context->getBackendSession();
        $this->_storeManager   = $context->getStoreManager();
        $this->_objectManager  = $context->getObjectManager();
        parent::__construct
        (
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);

        return $this->_storeManager->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $programId  = $this->getRequest()->getParam('program_id');
        $storeId    = $this->getStore()->getId();
        $collection = $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\Transaction')->getCollection()
            ->addFieldToFilter('main_table.program_id', $programId);

        $collection->getSelect()->join(
            [
                'transaction' => $collection->getTable('magestore_affiliateplus_transaction')
            ],
            'main_table.transaction_id = transaction.transaction_id',
            [
                'customer_id',
                'customer_email',
                'created_time',
                'status',
            ]
        );

        if ($storeId) {
            $collection->addFieldToFilter('transaction.store_id', $storeId);
        }
        $collection->getSelect()
            ->columns(
                [
                    'order_number' => 'transaction.order_number'
                ]
            )
            ->columns(
                [
                    'order_item_names' => 'transaction.order_number'
                ]
            );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {

        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

        $this->addColumn(
            'transaction_id',
            [
                'header'       => __('ID'),
                'sortable'     => true,
                'index'        => 'transaction_id',
                'width'        => '60',
                'filter_index' => 'main_table.transaction_id'
            ]
        );
        $this->addColumn(
            'transaction_account_name',
            [
                'header'       => __('Affiliate Account'),
                'index'        => 'account_name',
                'width'        => '150px',
                'align'        => 'right',
                'renderer'     => 'Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Renderer\Account',
                'filter_index' => 'main_table.account_name',
            ]
        );
        $this->addColumn(
            'transaction_customer_email',
            [
                'header'       => __('Customer Email Address'),
                'width'        => '150px',
                'index'        => 'customer_email',
                'align'        => 'right',
                'renderer'     => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Customer',
                'filter_index' => 'transaction.customer_email'
            ]
        );
        $this->addColumn(
            'transaction_order_number',
            [
                'header'       => __('Order ID'),
                'width'        => '150px',
                'index'        => 'order_number',
                'align'        => 'right',
                'renderer'     => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Order',
                'filter_index' => 'main_table.order_number',
            ]
        );
        $this->addColumn(
            'transaction_order_item_names',
            [
                'header'       => __('Product Name'),
                'width'        => '150px',
                'index'        => 'order_item_names',
                'align'        => 'left',
                'renderer'     => 'Magestore\Affiliateplus\Block\Adminhtml\Transaction\Renderer\Product',
                'filter_index' => 'main_table.order_item_names',
            ]
        );
        $this->addColumn(
            'transaction_total_amount',
            [
                'header'        => __('Order Subtotal'),
                'width'         => '150px',
                'index'         => 'total_amount',
                'align'         => 'right',
                'type'          => 'price',
                'currency_code' => $currencyCode,
                'filter_index'  => 'main_table.total_amount',
            ]
        );

        $this->addColumn(
            'transaction_commission',
            [
                'header'        => __('Commission'),
                'width'         => '150px',
                'align'         => 'right',
                'index'         => 'commission',
                'type'          => 'price',
                'currency_code' => $currencyCode,
            ]
        );

        $this->addColumn(
            'transaction_status',
            [
                'header'       => __('Transaction Status'),
                'align'        => 'right',
                'width'        => '150px',
                'index'        => 'status',
                'type'         => 'options',
                'options'      => [
                    1 => 'Complete',
                    2 => 'Pending',
                    3 => 'Canceled'
                ],
                'filter_index' => 'transaction.status'
            ]
        );

        $this->addColumn(
            'transaction_created_time',
            [
                'header'       => __('Date Created'),
                'width'        => '150px',
                'align'        => 'right',
                'index'        => 'created_time',
                'type'         => 'date',
                'filter_index' => 'transaction.created_time'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('affiliateplusadmin/transaction/view', ['transaction_id' => $row->getId()]);
    }

    /**
     * @return mixed
     */
    public function getGridUrl()
    {
        return $this->getUrl('affiliateplusadmin/program/transaction',
            [
                '_current' => true,
//                'transaction_id' =>$this->getRequest()->getParam('transaction_id'),
//                'store'		=>$this->getRequest()->getParam('store')
            ]
        );
    }

    /*=======Required methods=========*/
    public function getTabLabel()
    {
        return __('Account');
    }

    public function getTabTitle()
    {
        return __('Account');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
