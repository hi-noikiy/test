<?php
class MindMagnet_AddonPopup_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Check if product is already in cart
     *
     * @param int $productId
     * @return bool
     */
    public function isProductAlreadyInCart($productId)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $isInCart = false;
        foreach($quote->getAllVisibleItems() as $item) {
            if ($item->getProductId() == $productId) {
                $isInCart = true;
                break;
            }
        }

        return $isInCart;
    }
}