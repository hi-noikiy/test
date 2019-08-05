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
namespace Magestore\Affiliateplus\Block\Credit;

/**
 * Class Cart
 * @package Magestore\Affiliateplus\Block\Credit
 */
class Cart extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * get Helper
     *
     * @return \Magestore\Affiliateplus\Helper\Config
     */
    public function _getHelper(){
        return $this->_configHelper;
    }

    /**
     * get Account helper
     *
     * @return Magestore_Affiliateplus_Helper_Account
     */
    protected function _getAccountHelper() {
        return $this->_accountHelper;
    }

    /**
     * @return $this
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::credit/cart.phtml');
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormatedBalance() {
        $balance = $this->_getAccountHelper()->getAccount()->getBalance();
        $balance = $this->convertPrice($balance);
        if ($this->getAffiliateCredit() > 0) {
            if($balance >= $this->getAffiliateCredit()){
                $balance -= $this->getAffiliateCredit();
            }else {
                $this->setAffiliateCredit($balance);
                $balance -= $this->getAffiliateCredit();
            }
        }
        $balance = $this->_getHelper()->formatPrice($balance);
        return $balance;
    }

    /**
     * check using affiliate credit or not
     *
     * @return boolean
     */
    public function getUseAffiliateCredit() {
        return $this->_sessionCheckout->getUseAffiliateCredit();
    }

    /**
     * @return mixed
     */
    public function getAffiliateCredit() {
        return $this->_sessionCheckout->getAffiliateCredit();
    }

    /**
     * @return mixed
     */
    public function getBalance(){
        $balance = round($this->_getAccountHelper()->getAccount()->getBalance(), 2);
        return $balance;
    }

//    public function ConvertPrice($balance){
//        return $this->_getHelper()->ConvertPrice($balance);
//    }
}