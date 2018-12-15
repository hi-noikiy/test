<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Checkout_Onepage_Payment_Additional extends Mage_Core_Block_Template
{
    protected $_balanceModel = null;

    public function canShowBlock()
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            return false;
        }

        if (!$this->_getCustomer()->getId()) {
            return false;
        }

        if ($this->getBalance() < 0.0001) {
            return false;
        }

        if (!Mage::helper('amstcred')->isAllowedStoreCredit()) {
            return false;
        }

        return true;
    }

    public function getBalance()
    {
        if (!$this->_getCustomer()->getId()) {
            return 0;
        }
        return $this->_getBalanceModel()->getAmount();
    }

    public function getBalanceForUse()
    {
        $balance = $this->getBalance();
        if ($this->isCustomerBalanceUsed()) {
            $balance = $this->getQuote()->getBaseAmstcredAmountUsed();
        } else {
            //$balance = min($this->getQuote()->getBaseGrandTotal(), $balance);
        }
        return $balance;
    }

    public function isCustomerBalanceUsed()
    {
        return $this->getQuote()->getAmstcredUseCustomerBalance();
    }


    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }


    protected function _getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }


    protected function _getBalanceModel()
    {
        if (is_null($this->_balanceModel)) {
            $this->_balanceModel = Mage::getModel('amstcred/balance')
                ->setCustomer($this->_getCustomer())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

            //load customer balance for customer in case we have
            //registered customer and this is not guest checkout
            if ($this->_getCustomer()->getId()) {
                $this->_balanceModel->loadByCustomer();
            }
        }
        return $this->_balanceModel;
    }

}
