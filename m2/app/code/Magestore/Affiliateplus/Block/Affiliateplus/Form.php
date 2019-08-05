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
namespace Magestore\Affiliateplus\Block\Affiliateplus;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Form
 * @package Magestore\Affiliateplus\Block\Affiliateplus
 */
class Form extends \Magento\Payment\Block\Form
{

    protected $_grand_total = '';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_helperData;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magestore\Affiliateplus\Block\AbstractTemplate
     */
    protected $_abstractTemplate;

    /**
     * Form constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Affiliateplus\Helper\Data $helperData
     * @param \Magento\Directory\Model\Currency $currency
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Affiliateplus\Helper\Data $helperData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Affiliateplus\Block\AbstractTemplate $abstractTemplate,
        \Magento\Directory\Model\Currency $currency,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_helperData = $helperData;
        $this->_checkoutSession = $checkoutSession;
        $this->_abstractTemplate = $abstractTemplate;
        $this->_currency = $currency;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * @param null $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore($storeId = null)
    {
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return \Magento\Framework\App\ActionFlag|
     * @return \Magestore\Affiliateplus\Helper\Data
     */
    public function getHelperData()
    {
        return $this->_helperData;
    }

    /**
     * @param bool|false $dimension
     * @return mixed
     */
    public function getAffiliateplusDiscount($dimension = false)
    {
        $affiliateDicount = $this->_checkoutSession->getAffiliateplusDiscount();
        return $affiliateDicount;
    }

    /**
     * @return mixed
     */
    public function getAffiliateCreditAmount($dimension = false){
        $affiliatecredit = $this->_checkoutSession->getAffiliateCredit();
        return $affiliatecredit;
    }
    /**
     * @return mixed
     */
    public function getGrandTotal()
    {
        if (!$this->_grand_total) {
            $quote = $this->_helperData->getCheckoutSession()->getQuote();
            $grandTotal = $quote->getGrandTotal();
            $this->_grand_total = $grandTotal;
        }
        return $this->_grand_total;
    }

    /**
     * @return array
     */
    public function getAffiliateplusData()
    {
        $this->_helperData->getCheckoutSession()
            ->getQuote()
            ->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();
        $result = array();
        $result['affiliateDiscount'] = $this->getAffiliateplusDiscount(true);
        $result['affiliateCreditAmount'] = $this->getAffiliateCreditAmount();


        return $result;
    }





}
