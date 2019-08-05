<?php
namespace Ktpl\Wholesaler\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddtierdiscountToOrder implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $tier_discount = $quote->getTierDiscount();
        $basetier_discount = $quote->getBaseTierDiscount();
        if (!$tier_discount || !$basetier_discount) {
            return $this;
        }
        //Set fee data to order
        $order = $observer->getOrder();
        $order->setData('tier_discount', $tier_discount);
        $order->setData('base_tier_discount', $basetier_discount);

        return $this;
    }
}
