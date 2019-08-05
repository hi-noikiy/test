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
class Form extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    protected $_bank_accounts ='';
    /**
     * @param $value
     * @return $this
     */
    public function setPaymentMethod($value){
        $this->_payment_method = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod(){
        return $this->_payment_method;
    }

    /**
     * @return $this
     */
    public function _prepareLayout(){
        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     */
    public function getAmount(){
        return $this->getRequest()->getParam('amount');
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Payment\Verify
     */
    public function getModelPaymentVerify(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Verify');
    }
    /**
     * @return \Magestore\Affiliateplus\Helper\Payment
     */
    public function getPaymentHelper(){
        return $this->_paymentHelper;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager(){
        return $this->_storeManager;
    }

    /**
     * @param $key
     * @param $store
     * @return mixed
     */
    public function getConfig($key, $store){
        return $this->_dataHelper->getConfig($key, $store);
    }
}
