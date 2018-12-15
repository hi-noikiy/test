<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Sales_Order_Creditmemo_Controls extends Mage_Core_Block_Template
{

    public function canShowBlock()
    {
        return $this->hasIssetStoreCreditInOrder();
    }

    public function hasIssetStoreCreditInOrder($checkPaid = true)
    {
        $order = $this->_getCreditmemo()->getOrder();
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType()
                != Amasty_StoreCredit_Model_Catalog_Product_Type_StoreCredit::TYPE_STORECREDIT_PRODUCT
            ) {
                continue;
            }
            if (!$checkPaid) {
                return true;
            }
            $options = $item->getProductOptions();
            if (isset($options['amstcred_paid_invoice_items']) && count($options['amstcred_paid_invoice_items']) > 0) {
                return true;
            }
        }
        return false;
    }

    public function getCustomerId()
    {
        return $this->_getCreditmemo()->getCustomerId();
    }

    public function hasRefundToStoreCredit()
    {
        $hasRefund = Mage::helper('amstcred')->isModuleActive($this->_getCreditmemo()->getStoreId());
        $hasRefund = $hasRefund && !$this->_getCreditmemo()->getOrder()->getCustomerIsGuest();
        $hasRefund = $hasRefund && !$this->_hasOnlyStoreCreditProductInOrder();
        //$hasRefund = $hasRefund && !$this->hasIssetStoreCreditInOrder(false);;
        $hasRefund = $hasRefund && $this->getReturnValue() >= 0.0001;
        return $hasRefund;
    }

    public function getReturnValue()
    {
        return (float)$this->_getCreditmemo()->getBaseGrandTotal();
    }


    public function _hasOnlyStoreCreditProductInOrder()
    {
        $order = $this->_getCreditmemo()->getOrder();
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType()
                != Amasty_StoreCredit_Model_Catalog_Product_Type_StoreCredit::TYPE_STORECREDIT_PRODUCT
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    protected function _getCreditmemo()
    {
        return Mage::registry('current_creditmemo');
    }
}
