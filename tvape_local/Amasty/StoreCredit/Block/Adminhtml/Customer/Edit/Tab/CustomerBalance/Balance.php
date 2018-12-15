<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Customer_Edit_Tab_CustomerBalance_Balance extends Mage_Adminhtml_Block_Template
{
    public function isSingleStoreMode()
    {
        return Mage::app()->isSingleStoreMode();
    }

    public function getCustomerBalance()
    {
        return $collection = Mage::getModel('amstcred/balance')
            ->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'))->getFirstItem();

    }
}
