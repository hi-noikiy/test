<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Block\Adminhtml\Sales\Order\Create;

use Magento\Framework\View\Element\Template;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Backend\Model\Session\Quote;

/**
 * Class Payment
 *
 * @package Aheadworks\StoreCredit\Block\Adminhtml\Sales\Order\Create
 */
class Payment extends Template
{
    /**
     * @var Create
     */
    private $orderCreate;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditService;

    /**
     * @var Quote
     */
    private $sessionQuote;

    /**
     * @param Context $context
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     * @param Session\Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        Context $context,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        CustomerStoreCreditManagementInterface $customerStoreCreditService,
        Quote $sessionQuote,
        array $data = []
    ) {
        $this->orderCreate = $orderCreate;
        $this->priceCurrency = $priceCurrency;
        $this->customerStoreCreditService = $customerStoreCreditService;
        $this->sessionQuote = $sessionQuote;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->orderCreate->getQuote();
    }

    /**
     * Show store credit or not
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->getBalance() > 0;
    }

    /**
     * Retrieve customer balance
     *
     * @return float
     */
    public function getBalance()
    {
        if (!$this->getQuote() || !$this->getQuote()->getCustomerId()) {
            return 0.0;
        }
        return $this->customerStoreCreditService
            ->getCustomerStoreCreditBalance($this->getQuote()->getCustomerId());
    }

    /**
     * Format value as price
     *
     * @param float $value
     * @return string
     */
    public function formatPrice($value)
    {
        return $this->priceCurrency->convertAndFormat(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->sessionQuote->getStore()
        );
    }

    /**
     * Check whether quote uses customer balance
     *
     * @return bool
     */
    public function isUseAwStoreCredit()
    {
        return $this->getQuote()->getAwUseStoreCredit();
    }
}
