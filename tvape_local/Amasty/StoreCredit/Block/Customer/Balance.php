<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Customer_Balance extends Mage_Core_Block_Template
{
    /**
     * @return Amasty_StoreCredit_Model_Balance
     */
    public function getCustomerBalance()
    {
        //$website_id = Mage::app()->getStore()->getWebsiteId();
        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
        return Mage::getModel('amstcred/balance')->setCustomerId($customer_id)->loadByCustomer();
    }
}
