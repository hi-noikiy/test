<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class SetRefundToStoreCredit
 *
 * @package Aheadworks\StoreCredit\Observer
 */
class SetRefundToStoreCredit implements ObserverInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param RequestInterface $request
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        RequestInterface $request
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->request = $request;
    }

    /**
     * Set refund flag to creditmemo based on user input
     * used for event: adminhtml_sales_order_creditmemo_register_before
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getActionName() == 'updateQty') {
            return $this;
        }

        $input = $observer->getEvent()->getInput();
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();

        if (isset($input['refund_to_store_credit_enable']) && isset($input['refund_to_store_credit'])) {
            $enable = $input['refund_to_store_credit_enable'];
            $amount = $input['refund_to_store_credit'];
            if ($enable && is_numeric($amount)) {
                if ((string)(float)$amount > (string)(float)$creditMemo->getBaseAwStoreCreditRefundValue()) {
                    $maxAllowedAmount = $order->getBaseCurrency()
                        ->format($creditMemo->getBaseAwStoreCreditRefundValue(), null, false);
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Maximum Store Credit amount allowed to refund is: %1', $maxAllowedAmount)
                    );
                } else {
                    $amount = $this->priceCurrency->round($amount);
                    $creditMemo->setBaseAwStoreCreditRefunded($amount);

                    $amount = $this->priceCurrency->round(
                        $amount * $creditMemo->getOrder()->getBaseToOrderRate()
                    );
                    $creditMemo->setAwStoreCreditRefunded($amount);
                }
            }
        }

        return $this;
    }
}
