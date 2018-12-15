<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Checkout_Cart_Total extends Mage_Checkout_Block_Total_Default
{
    protected $_template = 'amasty/amstcred/checkout/cart/total.phtml';

    /*public function getQuoteStoreCredit()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        return $quote->getAmstcredAmountUsed();
    }*/
}
