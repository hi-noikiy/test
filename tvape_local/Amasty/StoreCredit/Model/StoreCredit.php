<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_StoreCredit extends Mage_Core_Model_Abstract
{
    const OPEN_AMOUNT_NO = 0;
    const OPEN_AMOUNT_YES = 1;

    const PRICE_TYPE_EQUAL = 0;
    const PRICE_TYPE_PERCENT = 1;


    /**
     * @param Varien_Event_Observer $observer
     */
    public function replaceRendererPrices(Varien_Event_Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        $priceElement = $form->getElement('amstcred_amount');
        if ($priceElement) {
            $priceElement->setRenderer(Mage::app()->getLayout()->createBlock('amstcred/adminhtml_renderer_price'));
        }

    }
}
