<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class Aheadworks\StoreCredit\Observer\RedeemForOrder
 */
class RedeemForOrder implements ObserverInterface
{
    /**
     *  {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var $order \Magento\Sales\Model\Order **/
        $order = $event->getOrder();
        /** @var $quote \Magento\Quote\Model\Quote $quote */
        $quote = $event->getQuote();

        if ($quote->getAwUseStoreCredit()) {
            $order->setAwUseStoreCredit($quote->getAwUseStoreCredit());
            $order->setAwStoreCreditAmount($quote->getAwStoreCreditAmount());
            $order->setBaseAwStoreCreditAmount($quote->getBaseAwStoreCreditAmount());
        }
    }
}
