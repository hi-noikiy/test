<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2014 Amasty (http://www.amasty.com)
 * @package Amasty_Amautoshipping
 */
class Amasty_Autoshipping_Model_Observer
{

    public function handleCollect($observer)
    {
        if (!Mage::getStoreConfig('amautoshipping/general/enable'))
            return $this;

        $quote = $observer->getEvent()->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        if (!$shippingAddress->getCountryId()) {
            $settings = Mage::getStoreConfig('amautoshipping/address');
            foreach ($settings as $k => $v) {
                $shippingAddress->setData($k, $v);
            }
            $method = Mage::getStoreConfig('amautoshipping/general/shipping_method');
            $shippingAddress
                ->setShippingMethod($method)
                ->setCollectShippingRates(true)
            ;
            $shippingAddress->save();
            $quote->save();
        }

        return $this;
    }
}