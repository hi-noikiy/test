<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Sales_Order_Create_Payment extends Mage_Core_Block_Template
{

    protected $_balanceModel = null;

    public function canShowBlock()
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            return false;
        }

        if (!Mage::helper('amstcred')->isAllowedStoreCredit()) {
            return false;
        }

        if (!$this->_getCustomer()->getId()) {
            return false;
        }

        if ($this->getBalance() < 0.0001) {
            return false;
        }
        return true;
    }

    public function isCustomerBalanceUsed()
    {
        return $this->getQuote()->getAmstcredUseCustomerBalance();
    }


    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_getOrderCreateModel()->getQuote();
    }

    public function getBalance()
    {
        //return $this->getQuote()->getStore()->convertPrice($this->_getBalanceModel()->getAmount(), true, false);
        return $this->_getBalanceModel()->getAmount();
    }

    protected function _getCustomer()
    {
        return $this->getQuote()->getCustomer();
    }


    protected function _getBalanceModel()
    {
        if (is_null($this->_balanceModel)) {
            $this->_balanceModel = Mage::getModel('amstcred/balance')
                ->setCustomer($this->_getCustomer())
                ->setWebsiteId($this->getQuote()->getStore()->getWebsiteId());

            //load customer balance for customer in case we have
            //registered customer and this is not guest checkout
            if ($this->_getCustomer()->getId()) {
                $this->_balanceModel->loadByCustomer();
            }
        }
        return $this->_balanceModel;
    }

    /**
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }
}
