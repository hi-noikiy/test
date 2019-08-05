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
namespace Magestore\Affiliateplus\Block\Payment;
/**
 * Class Confirm
 * @package Magestore\Affiliateplus\Block\Payment
 */
class ListPayment extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     *  contruct function
     */
    protected function _construct() {
        parent::_construct();
        $account = $this->_sessionModel->getAccount();
        $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment')->getCollection()
            ->addFieldToFilter('store_ids', ['finset' => $this->_storeManager->getStore()->getId()])
            ->addFieldToFilter('account_id', $account->getId())
            ->setOrder('request_time', 'DESC');

        $this->_eventManager->dispatch('affiliateplus_prepare_payments_collection', [
            'collection' => $collection,
        ]
    );

        $this->setCollection($collection);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout() {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'payments_pager')
            ->setTemplate('Magestore_Affiliateplus::html/pager.phtml')
            ->setCollection($this->getCollection());
        $this->setChild('payments_pager', $pager);

        $grid = $this->getLayout()->createBlock('Magestore\Affiliateplus\Block\Grid', 'payments_grid');
        $grid->addColumn('id', [
                'header' => __('No.'),
                'align' => 'left',
                'render' => 'getNoNumber',
            ]
        );

        $grid->addColumn('request_time',
            [
                'header' => __('Date Requested'),
                'index' => 'request_time',
                'type' => 'date',
                'format' => \IntlDateFormatter::MEDIUM,
                'align' => 'left',
                'width' => '150px',
                'render' => 'getDate',
                'searchable' => true,
            ]
        );

        $grid->addColumn('amount',
            [
                'header' => __('Amount'),
                'align' => 'left',
                'type' => 'price',
                'index' => 'amount',
                'render' => 'getAmount',
                'searchable' => true,
            ]
        );

        $grid->addColumn('tax_amount',
            [
                'header' => __('Tax'),
                'align' => 'left',
                'type' => 'price',
                'index' => 'tax_amount',
                'render' => 'getTaxAmount',
                'searchable' => true,
            ]
        );

        $grid->addColumn('fee',
            [
                'header' => __('Fee'),
                'align' => 'left',
                'type' => 'price',
                'index' => 'fee',
                'render' => 'getFeeRow',
                'searchable' => true,
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_prepare_payments_columns',
            [
                'grid' => $grid,
            ]
        );

        $grid->addColumn('status',
            [
                'header' => __('Status'),
                'align' => 'left',
                'index' => 'status',
                'type' => 'options',
                'options' => [
                    1 => __('Pending'),
                    2 => __('Processing'),
                    3 => __('Complete'),
                    4 => __('Canceled'),
                ],
                'width' => '95px',
                'searchable' => true,
             ]
        );

        $grid->addColumn('action',
            [
                'header' => __('Action'),
                'align' => 'left',
                'type' => 'action',
                'render' => 'getPaymentAction',
                'action' => [
                    'label' => __('View'),
                    'url' => 'affiliateplus/index/viewPayment',
                    'name' => 'id',
                    'field' => 'payment_id',
                ]
            ]
        );

        $this->setChild('payments_grid', $grid);
        return $this;
    }

    /**
     * @param $row
     * @return string
     */
    public function getNoNumber($row) {
        return sprintf('#'.$row->getId());
    }

    /**
     * @param $row
     * @return \Magento\Framework\Phrase
     */
    public function getFeeRow($row) {
        $fee = $row->getFee();
        if ($row->getIsPayerFee())
            $fee = 0;
        return $this->formatPrice($fee);
    }

    /**
     * @param $row
     * @return \Magento\Framework\Phrase
     */
    public function getTaxAmount($row) {
        $taxamount = $row->getTaxAmount();
        return $this->formatPrice($taxamount);
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getDate($row) {
        $requestDate = $row->getRequestTime();
        return $this->formatDate($requestDate, \IntlDateFormatter::MEDIUM, false);
    }

    /**
     * @param $row
     * @return \Magento\Framework\Phrase
     */
    public function getAmount($row) {
        $amount = $row->getAmount();
        return $this->formatPrice($amount);
    }
    /**
     * @param $row
     * @return string
     */
    public function getPaymentAction($row) {
        $confirmText = __('Are you sure?');
        $cancelurl=$this->getUrl('affiliateplus/index/cancelPayment', ['id' => $row->getPaymentId()]);
        $action = '<a href="' . $this->getUrl('affiliateplus/index/viewPayment', ['id' => $row->getPaymentId()]) . '">' . __('View') . '</a>';

        $limitDays = intval($this->_getHelper()->getPaymentConfig('cancel_days'));
        $canCancel = $limitDays ? (time() - strtotime($row->getRequestTime()) <= $limitDays * 86400) : true;
        if ($row->getStatus() <= 2 && $canCancel)
            $action .=' | <a href="javascript:void(0)" onclick="cancelPayment'.$row->getPaymentId().'()">' . __('Cancel') . '</a>
                <script type="text/javascript">
                    //<![CDATA[
                        function cancelPayment'.$row->getPaymentId().'(){
                            if (confirm(\''.$confirmText.'\')){
                                setLocation(\''.$cancelurl.'\');
                            }
                        }
                    //]]>
                </script>';
        return $action;
    }

    /**
     * @return string
     */
    public function getPagerHtml() {
        return $this->getChildHtml('payments_pager');
    }

    /**
     * @return string
     */
    public function getGridHtml() {
        return $this->getChildHtml('payments_grid');
    }

    /**
     * @return string
     */
    protected function _toHtml() {
        $this->getChildBlock('payments_grid')->setCollection($this->getCollection());
        return parent::_toHtml();
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
    /**
     * @return \Magestore\Affiliateplus\Helper\Config
     */
    public function _getHelper() {
        return $this->_objectManager->get('Magestore\Affiliateplus\Helper\Config');
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Account
     */
    public function getAccountHelper()
    {
        return $this->_accountHelper;
    }
}
