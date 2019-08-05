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
class Request extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    protected $_all_payment_method = '';
    protected $_tax_rate = '';
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout(){
        parent::_prepareLayout();

        $paymentMethods = $this->getAllPaymentMethod();

        foreach ($paymentMethods as $code => $method){
            $paymentMethodFormBlock = $this->getLayout()->createBlock($method->getFormBlockType(),"payment_method_form_$code")->setPaymentMethod($method);
            $this->setChild("payment_method_form_$code",$paymentMethodFormBlock);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllPaymentMethod(){
        if (!$this->_all_payment_method){
            $this->_all_payment_method = $this->_paymentHelper->getAvailablePayment();
        }
        return $this->_all_payment_method;
    }

    /**
     * @return mixed
     */
    public function getAmount(){
        if($this->getRequest()->getParam('amount'))
            return $this->getRequest()->getParam('amount');
        $paymentSession = $this->_sessionModel->getPayment();
        if($paymentSession)
            if($paymentSession->getAmount())
                return $paymentSession->getAmount();
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
        if($this->_paymentHelper->getBalanceConfig() == 'website') {
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
     * get Format Balance
     *
     * @return mixed
     */
    public function getFormatedBalance(){
        $balance = 0;
        if($this->_paymentHelper->getBalanceConfig() == 'website') {
            $website = $this->_storeManager->getStore()->getWebsite();

            $stores = $website->getStores();

            foreach($stores as $store) {
                $account = $this->_accountFactory->create()->setStoreId($store->getId())->load($this->getAccount()->getId());
                $balance += $account->getBalance();
            }
            return $this->formatPrice($balance);
        } else {
            $balance = $this->getAccount()->getBalance();
            return $this->formatPrice($balance);
        }
    }

    /**
     * get Format Action Url
     *
     * @return string
     */
    public function getFormActionUrl(){
        $url = $this->getUrl('affiliateplus/index/confirmRequest');
        return $url;
    }

    /**
     * get Tax rate when withdrawal
     *
     * @return float
     */
    public function getTaxRate() {
        if (!$this->_tax_rate) {
            $this->_tax_rate = $this->_taxHelper->getTaxRate();
        }
        return $this->_tax_rate;
    }

    /**
     * @return bool
     */
    public function includingFee() {
        return ($this->_paymentHelper->getPayFeeConfig() != 'payer');
    }

    /**
     * @return mixed
     */
    public function getPriceFormatJs() {
        $priceFormat = $this->_formatLocale->getPriceFormat();
        return $this->_jsonHelper->jsonEncode($priceFormat);
    }

    /**
     * get default payment method
     * @return type
     */
    protected function _getDefaultPaymentMethod(){
        return $this->_dataHelper->getConfig('affiliateplus/payment/default_method');
    }

    /**
     * check a method is default or not
     * @param type $code
     * @return boolean
     */
    public function methodSelected($code){
        if($code == $this->_getDefaultPaymentMethod()){
            return true;
        }
        return false;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return string
     */
    public function toCurrency($balance){
        return $this->_currencyInterface->toCurrency($balance, $options = []);
    }
}
