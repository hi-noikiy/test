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
namespace Magestore\Affiliateplus\Block\Adminhtml\Selectaccount;
use Magestore\Affiliateplus\Model\Account;
/**
 * Class Grid
 * @package Magestore\Account\Block\Adminhtml\Selectaccount
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
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = array()
    )
    {
        parent::__construct($context, $backendHelper, $data);
        $this->_eventManager = $context->getEventManager();
        $this->_objectManager = $objectManager;
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
        $this->setId('accountGrid');
        $this->setDefaultSort('account_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {

        $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Account\Collection');
        $this->_eventManager->dispatch('affiliateplus_adminhtml_join_account_other_table', array('collection' => $collection));

        $storeId = $this->getRequest()->getParam('store');
        $collection->setStoreViewId($storeId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        $this->addColumn('account_id',
            [
                'header'    => __('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'account_id',
                'type'	  => 'number',
                'filter_index'	=> 'main_table.account_id'
            ]
        );

        $this->addColumn('name',
            [
                'header'    => __('Name'),
                'align'     =>'left',
                'index'     => 'name',
                'filter_index'	=> 'main_table.name'
            ]
        );

        $this->addColumn('email',
            [
                'header'    => __('Email Address'),
                'index'     => 'email',
                'filter_index'	=> 'main_table.email'
            ]
        );

        $this->addColumn('balance',
            [
                'header'    => __('Balance'),
                'width'     => '100px',
                'align'     =>'right',
                'index'     => 'balance',
                'type'		=> 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'filter_index'	=> 'main_table.balance'
            ]
        );

        $this->addColumn('total_commission_received',
            [
                'header'    => __('Total Commission'),
                'width'     => '100px',
                'align'     =>'right',
                'index'     => 'total_commission_received',
                'type'		=> 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'filter_index'	=> 'main_table.total_commission_received'
            ]
        );

        $this->addColumn('total_paid',
            [
                'header'    => __('Commission Paid'),
                'width'     => '100px',
                'align'     =>'right',
                'index'     => 'total_paid',
                'type'		=> 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'filter_index'	=> 'main_table.total_paid'
            ]
        );


       $this->_eventManager->dispatch('affiliateplus_adminhtml_add_column_account_grid', array('grid' => $this));

        $this->addColumn('status',
            [
                'header'    => __('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'status',
                'filter_index'	=> 'main_table.status',
                'type'      => 'options',
                'options'   => array(
                    Account::ACCOUNT_ENABLED => __('Enabled'),
                    Account::ACCOUNT_DISABLED => __('Disabled'),
                ),
            ]
        );

        $this->addColumn('approved',
            [
                'header'    => __('Approved'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'approved',
                'filter_index'	=> 'main_table.approved',
                'type'      => 'options',
                'options'   => array(
                    Account::ACCOUNT_APPROVED_YES => __('Yes'),
                    Account::ACCOUNT_APPROVED_NO => __('No'),
                ),
            ]
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/new', array('account_id' => $row->getId(), 'store' => $this->getRequest()->getParam('store')));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/selectaccountgrid', array('_current'=>true));
    }
}
