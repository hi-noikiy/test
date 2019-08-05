<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Aheadworks\StoreCredit\Observer\Refund
 */
class Refund implements ObserverInterface
{
    /**
     * Set refund amount to credit memo
     * used for event: sales_order_credit_memo_refund
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();

        if ($creditMemo->getBaseAwStoreCreditRefunded()) {
            $order->setBaseAwStoreCreditRefunded(
                $order->getBaseAwStoreCreditRefunded() + $creditMemo->getBaseAwStoreCreditRefunded()
            );
            $order->setAwStoreCreditRefunded(
                $order->getAwStoreCreditRefunded() + $creditMemo->getAwStoreCreditRefunded()
            );
        }

        if ($creditMemo->getBaseAwStoreCreditAmount()) {
            $creditMemo->setBaseAwStoreCreditReimbursed(abs($creditMemo->getBaseAwStoreCreditAmount()));
            $creditMemo->setAwStoreCreditReimbursed(abs($creditMemo->getAwStoreCreditAmount()));
            $order->setBaseAwStoreCreditReimbursed(
                $order->getBaseAwStoreCreditReimbursed() + abs($creditMemo->getBaseAwStoreCreditAmount())
            );
            $order->setAwStoreCreditReimbursed(
                $order->getAwStoreCreditReimbursed() + abs($creditMemo->getAwStoreCreditAmount())
            );

            // We need to update flag after credit memo was refunded and order's properties changed
            if ($order->getAwStoreCreditInvoiced() < 0
                && $order->getAwStoreCreditInvoiced() == -$order->getAwStoreCreditReimbursed()
            ) {
                $order->setForcedCanCreditmemo(false);
            }
        }

        return $this;
    }
}
