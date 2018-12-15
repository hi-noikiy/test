<?php

class MindMagnet_Sales_Model_Quote_Address_Total_Subtotal extends Mage_Sales_Model_Quote_Address_Total_Subtotal
{
    /**
     * Address item initialization
     *
     * @param  $item
     * @return bool
     */
    protected function _initItem($address, $item)
    {

        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
        }
        else {
            $quoteItem = $item;
        }
        $product = $quoteItem->getProduct();
        $product->setCustomerGroupId($quoteItem->getQuote()->getCustomerGroupId());

        /**
         * Quote super mode flag mean what we work with quote without restriction
         */
        if ($item->getQuote()->getIsSuperMode()) {
            if (!$product) {
                return false;
            }
        }
        else {
            if (!$product || !$product->isVisibleInCatalog()) {
                return false;
            }
        }

        $isUpSell = $this->isUpSell($item);
        $isAddonPopup = $this->isAddOnPopup($item);

        if ($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
            $finalPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                $quoteItem->getParentItem()->getProduct(),
                $quoteItem->getParentItem()->getQty(),
                $quoteItem->getProduct(),
                $quoteItem->getQty()
            );
            $newFinalPrice = $this->getAddOnPrice($product, $finalPrice, $isUpSell, $isAddonPopup);
            $item->setPrice($newFinalPrice)
                ->setBaseOriginalPrice($newFinalPrice);
            $item->calcRowTotal();
        } else if (!$quoteItem->getParentItem()) {
            $finalPrice = $product->getFinalPrice($quoteItem->getQty());
            $newFinalPrice = $this->getAddOnPrice($product, $finalPrice, $isUpSell, $isAddonPopup);
            $item->setPrice($newFinalPrice)
                ->setBaseOriginalPrice($newFinalPrice);
            $item->calcRowTotal();
            $this->_addAmount($item->getRowTotal());
            $this->_addBaseAmount($item->getBaseRowTotal());
            $address->setTotalQty($address->getTotalQty() + $item->getQty());
        }

        return true;
    }

    private function getAddOnPrice(Mage_Catalog_Model_Product $product, $finalPrice, $isUpsell = false, $isAddonPopup = false)
    {

        if ($isUpsell) {
            $addOnPrice = $product->getResource()->getAttributeRawValue($product->getId(),'addons_special_price', Mage::app()->getStore()->getId());
            return ($addOnPrice > 0 ? $addOnPrice : $finalPrice);
        } elseif ($isAddonPopup) {
            $addOnPopupPrice = $product->getResource()->getAttributeRawValue($product->getId(),'addons_popup_special_price', Mage::app()->getStore()->getId());
            return ($addOnPopupPrice > 0 ? $addOnPopupPrice : $finalPrice);
        } else {
            return $finalPrice;
        }
    }

    private function isUpSell(Mage_Sales_Model_Quote_Item $item)
    {
        $infoBuy = array();


        if ($item->getId()) {
            $infoBuy = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
        }

        if (isset($infoBuy['from_upsell']) && $infoBuy['from_upsell'] == '1') {
            return true;
        }

        return false;
    }


    private function isAddOnPopup(Mage_Sales_Model_Quote_Item $item)
    {
        $infoBuy = array();


        if ($item->getId()) {
            $infoBuy = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
        }

        if (isset($infoBuy['from_addon_popup']) && $infoBuy['from_addon_popup'] == '1') {
            return true;
        }

        return false;
    }
}