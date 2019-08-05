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
 * Class Account
 * @package Magestore\Affiliateplusprogram\Block\Adminhtml\Program\Edit\Tab
 */
class Account extends \Magento\Backend\Block\Widget\Grid\Extended implements TabInterface
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
        $this->setId('accountGrid');
        $this->setDefaultSort('account_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getProgram() && $this->getProgram()->getId()) {
            $this->setDefaultFilter(
                [
                    'in_accounts' => 1
                ]
            );
        }
    }


    /**
     * @param Column $column
     *
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_accounts') {
            $accountIds = $this->_getSelectedAccounts();
            if (empty($accountIds)) {
                $accountIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('account_id', ['in' => $accountIds]);
            } elseif ($accountIds) {
                $this->getCollection()->addFieldToFilter('account_id', ['nin' => $accountIds]);
            }
            return $this;
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
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
        $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\Account')->getCollection();
        $storeId    = $this->getStore()->getId();
        if ($storeId) {
            $collection->setStoreViewId($storeId);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return array
     */
    protected function _getSelectedAccounts()
    {
        $accounts = $this->getRequest()->getParam('oaccount');
        if (!is_array($accounts)) {
            $accounts = array_keys($this->getSelectedRelatedAccounts());
        }
        return $accounts;
    }

    /**
     * @return array
     */
    public function getSelectedRelatedAccounts()
    {
        $accounts          = array();
        $program           = $this->getProgram();
        $accountCollection = $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\ResourceModel\Account\Collection')
            ->addFieldToFilter(
                'program_id', $program->getId()
            );
        foreach ($accountCollection as $account) {
            $accounts[$account->getAccountId()] = ['position' => 0];
        }
        return $accounts;
    }

    /**
     * @return mixed
     */
    public function getProgram()
    {
        $id = $this->getRequest()->getParam('program_id');
        return $this->_objectManager->create('Magestore\Affiliateplusprogram\Model\Program')
            ->load($id);
    }


    protected function _prepareColumns()
    {
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

        $this->addColumn(
            'in_accounts',
            [
                'type'             => 'checkbox',
                'name'             => 'in_accounts',
                'values'           => $this->_getSelectedAccounts(),
                'index'            => 'account_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction',
                'align'            => 'center',
            ]
        );
        $this->addColumn(
            'account_id',
            [
                'header'   => __('ID'),
                'sortable' => true,
                'index'    => 'account_id',
                'width'    => '60',
            ]
        );
        $this->addColumn(
            'account_name',
            [
                'header' => __('Name'),
                'index'  => 'name',
                'align'  => 'left'
            ]
        );
        $this->addColumn(
            'account_email',
            [
                'header' => __('Email Address'),
                'index'  => 'email',
                'align'  => 'left'
            ]
        );

        $this->addColumn(
            'account_balance',
            [
                'header'        => __('Balance'),
                'width'         => '100px',
                'align'         => 'right',
                'index'         => 'balance',
                'type'          => 'price',
                'currency_code' => $currencyCode,
            ]
        );

        $this->addColumn(
            'account_total_commission_received',
            [
                'header'        => __('Total Commission Received'),
                'align'         => 'left',
                'index'         => 'total_commission_received',
                'type'          => 'price',
                'currency_code' => $currencyCode,
            ]
        );

        $this->addColumn(
            'account_total_paid',
            [
                'header'        => __('Commission Paid'),
                'align'         => 'left',
                'index'         => 'total_paid',
                'type'          => 'price',
                'currency_code' => $currencyCode,
            ]
        );

        $this->addColumn(
            'account_status',
            [
                'header'  => __('Status'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => [
                    1 => 'Enabled',
                    2 => 'Disabled',
                ],
                'filter_index' => 'main_table.status'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header'   => __('Position'),
                'type'     => 'number',
                'index'    => 'position',
                'editable' => true,
                'filter'   => false,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('affiliateplusadmin/account/edit',
            [
                'account_id' => $row->getId(),
                'store'      => $this->getRequest()->getParam('store')
            ]
        );
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/accountGrid',
            [
                '_current'   => true,
                'account_id' => $this->getRequest()->getParam('account_id'),
                'store'      => $this->getRequest()->getParam('store')
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
