<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Total\Quote;

use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Aheadworks\StoreCredit\Model\Config;

/**
 * Class Aheadworks\StoreCredit\Model\Quote\Address\Total\StoreCredit
 */
class StoreCredit extends AbstractTotal
{
    /**
     * @var boolean
     */
    private $isFirstTimeResetRun = true;

    /**
     * @var CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditService;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config $config
     */
    public function __construct(
        CustomerStoreCreditManagementInterface $customerStoreCreditService,
        PriceCurrencyInterface $priceCurrency,
        Config $config
    ) {
        $this->setCode('aw_store_credit');
        $this->customerStoreCreditService = $customerStoreCreditService;
        $this->priceCurrency = $priceCurrency;
        $this->config = $config;
    }

    /**
     *  {@inheritDoc}
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();

        $this->reset($total, $quote, $address);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $customerId = $quote->getCustomerId();
        $storeCredit = $quote->getAwUseStoreCredit();

        if (!$customerId || !$storeCredit) {
            $quote->setAwUseStoreCredit(false);
            return $this;
        }

        $maxTotalPriceForStoreCredit = $total->getBaseGrandTotal();
        if (!$this->config->isApplyingStoreCreditOnTax($quote->getStore()->getWebsiteId())) {
            $maxTotalPriceForStoreCredit -= $total->getBaseTaxAmount();
        }
        if (!$this->config->isApplyingStoreCreditOnShipping($quote->getStore()->getWebsiteId())) {
            $maxTotalPriceForStoreCredit -= $total->getBaseShippingAmount();
        }
        if (!$maxTotalPriceForStoreCredit) {
            $quote->setAwUseStoreCredit(false);
            return $this;
        }
        $storeCreditForUse = $this->customerStoreCreditService
            ->calculateSpendStoreCredit($customerId, $maxTotalPriceForStoreCredit);

        if ($storeCreditForUse > 0) {
            if ($storeCreditForUse > $maxTotalPriceForStoreCredit) {
                $storeCreditForUse = $maxTotalPriceForStoreCredit;
            }

            $this->addStoreCreditToTotal(
                $this->priceCurrency->convertAndRound($storeCreditForUse, $quote->getStoreId()),
                $storeCreditForUse
            );

            $total->setSubtotalWithDiscount(
                $total->getSubtotal() + $total->getAwStoreCreditAmount()
            );
            $total->setBaseSubtotalWithDiscount(
                $total->getBaseSubtotal() + $total->getBaseAwStoreCreditAmount()
            );
            $total->setGrandTotal($total->getGrandTotal() - abs($total->getAwStoreCreditAmount()));
            $total->setBaseGrandTotal($total->getBaseGrandTotal() - abs($total->getBaseAwStoreCreditAmount()));
        }

        $quote->setAwStoreCreditAmount($total->getAwStoreCreditAmount());
        $quote->setBaseAwStoreCreditAmount($total->getBaseAwStoreCreditAmount());

        $address->setAwUseStoreCredit($quote->getAwUseStoreCredit());
        $address->setAwStoreCreditAmount($total->getAwStoreCreditAmount());
        $address->setBaseAwStoreCreditAmount($total->getBaseAwStoreCreditAmount());

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $result = null;
        $amount = $total->getAwStoreCreditAmount();

        if ($amount != 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Store Credit'),
                'value' => $amount,
            ];
        }
        return $result;
    }

    /**
     * Reset Store Credit total
     *
     * @param Total $total
     * @param Quote $quote
     * @param AddressInterface $address
     * @return StoreCredit
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function reset(Total $total, Quote $quote, AddressInterface $address)
    {
        if ($this->isFirstTimeResetRun) {
            $this->_addAmount(0);
            $this->_addBaseAmount(0);

            $quote->setAwStoreCreditAmount(0);
            $quote->setBaseAwStoreCreditAmount(0);

            $address->setAwUseStoreCredit(false);
            $address->setAwStoreCreditAmount(0);
            $address->setBaseAwStoreCreditAmount(0);

            $this->isFirstTimeResetRun = false;
        }
        return $this;
    }

    /**
     * Add Store Credit
     *
     * @param  float $storeCreditAmount
     * @param  float $baseStoreCreditAmount
     * @return StoreCredit
     */
    private function addStoreCreditToTotal(
        $storeCreditAmount,
        $baseStoreCreditAmount
    ) {
        $this->_addAmount(-$storeCreditAmount);
        $this->_addBaseAmount(-$baseStoreCreditAmount);

        return $this;
    }
}
