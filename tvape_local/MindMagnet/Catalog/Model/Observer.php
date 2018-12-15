<?php

class MindMagnet_Catalog_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addAdditionalPrice(Varien_Event_Observer $observer)
    {

        $product = $observer->getEvent()->getProduct();

        $additionalPrice = $product->getResource()->getAttributeRawValue($product->getId(),'addons_special_price',Mage::app()->getStore()->getId());

        $item = Mage::getSingleton('checkout/session')->getQuote()->getItemByProduct($product);
        $infoBuy = array();


        if ($item->getId()) {
            $infoBuy = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
        }

        if (isset($infoBuy['from_upsell']) && $infoBuy['from_upsell'] == '1' && $additionalPrice) {
            $product->setFinalPrice($additionalPrice);
        }

        return $this;

    }
}