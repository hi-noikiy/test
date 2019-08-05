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

namespace Magestore\Affiliateplus\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplus\Model\Transaction;
use Magestore\Affiliateplus\Observer\AbtractObserver;

/**
 * Class ProductGetFinalPrice
 * @package Magestore\Affiliateplus\Observer
 */
class SalesOrderPlaceAfter extends AbtractObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @var \Magestore\Affiliateplus\Helper\Data $helperData
     */
    protected $_helperData;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_backendQuoteSession;

    /**
     * OrderPlaceAfter constructor.
     *
     * @param \Magento\Checkout\Model\Session                     $checkoutSession
     * @param \Magento\Framework\Mail\Template\TransportBuilder   $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface  $inlineTranslation
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $sender
     * @param \Magento\Payment\Helper\Data                        $paymentHelper
     * @param array                                               $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $sender,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magestore\Affiliateplus\Helper\Data $helperData,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        array $data = []
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->_paymentHelper = $paymentHelper;
        $this->_sender = $sender;
        $this->_helperData = $helperData;
        $this->_backendQuoteSession = $backendQuoteSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->saveDiscountToOrder($order);
    }

    /**
     * save affiliate plus discount to order
     * @param $order
     */
    public function saveDiscountToOrder($order){
        $affiliateplusDiscount = $this->getCheckoutSession()->getData('affiliateplus_discount');
        $baseAffiliateplusDiscount = $this->getCheckoutSession()->getData('base_affiliateplus_discount');
        $accountIds =  $this->getCheckoutSession()->getData('account_id');
        $affiliateCredit = $this->getCheckoutSession()->getData('affiliateplus_credit');
        $baseAffiliateCredit = $this->getCheckoutSession()->getData('base_affiliateplus_credit');

        if ($affiliateplusDiscount && $baseAffiliateplusDiscount ) {
            $order->setAffiliateplusDiscount($affiliateplusDiscount);
            $order->setBaseAffiliateplusDiscount($baseAffiliateplusDiscount);
        }
        if($affiliateCredit && $baseAffiliateCredit){
            $order->setAffiliateCredit($affiliateCredit);
            $order->setBaseAffiliateCredit($baseAffiliateCredit);
            $order->setData('account_ids',$accountIds);
        }
        $this->getCheckoutSession()->setData('affiliateplus_discount', null);
        $this->getCheckoutSession()->setData('base_affiliateplus_discount', null);
        $this->getCheckoutSession()->setData('affiliateplus_credit', null);
        $this->getCheckoutSession()->setData('base_affiliateplus_credit', null);
        $this->getCheckoutSession()->setData('account_id', null);

    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession(){
        if($this->_helperData->isAdmin()){
            return $this->_backendQuoteSession;
        }
        return $this->_checkoutSession;
    }
}
