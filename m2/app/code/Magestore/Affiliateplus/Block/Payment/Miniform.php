<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\Payment;

/**
 * Class Request
 * @package Magestore\Affiliateplus\Block\Payment
 */
class Miniform extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @return $this
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::payment/miniform.phtml');
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccount(){
        return $this->_sessionModel->getAccount();
    }

    /**
     * @return float
     */
    public function getBalance(){
        $balance = 0;
        if($this->_dataHelper->getConfig('affiliateplus/account/balance') == 'website') {
            $website = $this->_storeManager->getStore()->getWebsite();

            $stores = $website->getStores();

            foreach($stores as $store) {
                $account = $this->_accountFactory->create()->setStoreId($store->getId())->load($this->getAccount()->getId());
                $balance += $account->getBalance();
            }
        } else {
            $balance = $this->getAccount()->getBalance();
        }
        $balance = $this->convertPrice($balance);
//        return floor($balance * 100) / 100; Gin fix
        return round($this->convertPrice($this->getAccount()->getBalance()),2);
    }

    /**
     * @return mixed
     */
    public function getFormatedBalance(){
        $balance = 0;
        if($this->_dataHelper->getConfig('affiliateplus/account/balance') == 'website') {
            $website = $this->_storeManager->getStore()->getWebsite();

            $stores = $website->getStores();

            foreach($stores as $store) {
                $account = $this->_accountFactory->create()->setStoreId($store->getId())->load($this->getAccount()->getId());
                $balance += $account->getBalance();
            }
            return $this->convertCurrency($balance);
        } else {
            return $this->convertCurrency($this->getAccount()->getBalance());
        }
    }


    /**
     * @return string
     */
    public function getFormActionUrl(){
        $url = $this->getUrl('affiliateplus/index/paymentForm');
        return $url;
    }

    /**
     * @return bool
     */
    public function canRequest() {
        return !$this->_accountHelper->disableWithdrawal();
    }

    /**
     * @return float
     */
    public function getMaxAmount() {
        $taxRate = $this->_taxHelper->getTaxRate();
        if (!$taxRate) {
            return $this->getBalance();
        }
        $balance = $this->getBalance();
        $maxAmount = $balance * 100 / (100 + $taxRate);
        return round($maxAmount, 2);
    }
}
