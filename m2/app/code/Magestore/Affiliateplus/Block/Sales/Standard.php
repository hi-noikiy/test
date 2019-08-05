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
namespace Magestore\Affiliateplus\Block\Sales;
/**
 * Class Standard
 * @package Magestore\Affiliateplus\Block\Sales
 */
class Standard extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * contruct
     */
    protected function _construct() {
        parent::_construct();
        $storeId = $this->_storeManager->getStore()->getId();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $account = $this->_sessionModel->getAccount();
        $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\Transaction')->getCollection();
        if ($this->_configHelper->getSharingConfig('balance', $storeId) == 'store')
            $collection->addFieldToFilter('store_id', $storeId);
        elseif($this->_configHelper->getSharingConfig('balance', $storeId) == 'website'){
            $storeIds = $this->_accountHelper->getStoreIdsByWebsite($websiteId);
            $collection->addFieldToFilter('store_id', ['in'=>$storeIds]);
        }
        $collection->addFieldToFilter('account_id', $account->getId())
            ->addFieldToFilter('type', '3')
            ->setOrder('created_time', 'DESC');

        $this->_eventManager->dispatch('affiliateplus_prepare_sales_collection', [
            'collection' => $collection,
        ]);

        $this->setCollection($collection);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout() {
        parent::_prepareLayout();
        $store = $this->_storeManager->getStore();
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'sales_pager')
            ->setTemplate('Magestore_Affiliateplus::html/pager.phtml')
            ->setCollection($this->getCollection());
        $this->setChild('sales_pager', $pager);

        $grid = $this->getLayout()->createBlock('Magestore\Affiliateplus\Block\Grid', 'sales_grid');

        // prepare column
        $grid->addColumn('id', [
            'header' => __('No.'),
            'align' => 'left',
            'render' => 'getNoNumber',
         ]
        );

        $grid->addColumn('created_time', [
            'header' => __('Date'),
            'index' => 'created_time',
            'type' => 'date',
            'format' => \IntlDateFormatter::MEDIUM,
            'align' => 'left',
            'width' => '220px',
            'searchable'    => true,
          ]
        );

        $grid->addColumn('order_item_names', [
            'header' => __('Products Name'),
            'index' => 'order_item_names',
            'align' => 'left',
            'render' => 'getFrontendProductHtmls',
            'searchable'    => true,
        ]
        );

        $grid->addColumn('total_amount', [
            'header' => __('Total Amount'),
            'align' => 'left',
            'type'      => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'total_amount',
        ]
        );

        $grid->addColumn('commission', [
            'header' => __('Commission'),
            'align' => 'left',
            'type' => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'commission',
        ]
        );

        $this->_eventManager->dispatch('affiliateplus_prepare_sales_columns_plus', [
            'grid'  => $grid,
        ]);

        $this->_eventManager->dispatch('affiliateplus_prepare_sales_columns', [
            'grid' => $grid,
        ]
        );

        $grid->addColumn('status', [
            'header' => __('Status'),
            'align' => 'left',
            'index' => 'status',
            'width' => '95px',
            'type' => 'options',
            'options' => [
                1 => __('Complete'),
                2 => __('Pending'),
                3 => __('Canceled'),
                4 => __('On Hold'),
            ],
            'searchable'    => true,
        ]
        );

        $this->setChild('sales_grid', $grid);
        return $this;
    }

    /**
     * @param $row
     * @return string
     */
    public function getNoNumber($row) {
        return sprintf('#%d', $row->getId());
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getFrontendProductHtmls($row) {
        return $this->_dataHelper->getFrontendProductHtmls($row->getData('order_item_ids'));
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getCommissionPlus($row) {
        $addCommission = $row->getPercentPlus() * $row->getCommission() / 100 + $row->getCommissionPlus();
        return $this->_currencyInterface->toCurrency($addCommission, $options = []);
    }

    /**
     * @return string
     */
    public function getPagerHtml() {
        return $this->getChildHtml('sales_pager');
    }

    /**
     * @return string
     */
    public function getGridHtml() {
        return $this->getChildHtml('sales_grid');
    }

    /**
     * @return string
     */
    protected function _toHtml() {
        $this->getChildBlock('sales_grid')->setCollection($this->getCollection());
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    public function getStatisticInfo() {
        $accountId = $this->_sessionModel->getAccount()->getId();
        $storeId = $this->_storeManager->getStore()->getId();
        $scope = $this->_dataHelper->getConfig('affiliateplus/account/balance', $storeId);

        $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\Transaction')->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('type', '3');

        $transactionTable = $this->_resource->getTableName('affiliatepluslevel_transaction');
        if ($this->_dataHelper->multilevelIsActive())
            $collection->getSelect()
                ->joinLeft(['ts' => $transactionTable], "ts.transaction_id = main_table.transaction_id", ['level' => 'level', 'plus_commission' => 'commission_plus'])
                ->columns("if (ts.commission IS NULL, main_table.commission, ts.commission) as commission")
                ->where("ts.tier_id=$accountId OR (ts.tier_id IS NULL AND main_table.account_id = $accountId )");
        if ($storeId && $scope == 'store')
            $collection->addFieldToFilter('store_id', $storeId);
        elseif($scope == 'website'){
            $websiteId = $this->_storeManager->getWebsite()->getId();
            $storeIds = $this->_accountHelper->getStoreIdsByWebsite($websiteId);
            $collection->addFieldToFilter('store_id', ['in'=>$storeIds]);
        }

        $totalCommission = 0;

        foreach ($collection as $item) {
            if ($item->getStatus() == 1) {
                $totalCommission += $item->getCommission();
                if ($item->getPlusCommission())
                    $totalCommission += $item->getPlusCommission();
                else
                    $totalCommission += $item->getCommissionPlus() + $item->getCommission() * $item->getPercentPlus() / 100;
            }
        }
        return [
            'number_commission' => count($collection),
            'transactions' => __('Sales Transactions'),
            'commissions' => $totalCommission,
            'earning' => __('Sales Earnings')
        ];
    }
}