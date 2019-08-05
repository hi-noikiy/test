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
namespace Magestore\Affiliateplus\Block\Payment\Offline;


/**
 * Class Request
 * @package Magestore\Affiliateplus\Block\Payment
 */
class Info extends \Magestore\Affiliateplus\Block\Payment\Form
{
    protected $_invoice_address = '';
    /**
     * @return $this
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::payment/offline/info.phtml');
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceAddress(){
        if(!$this->_invoice_address){
            $payment = $this->getPaymentMethod();
            $account = $this->_sessionModel->getAccount();
            $addressId = $payment->getAccountAddressId() ? $payment->getAccountAddressId() : $payment->getAddressId();
            $addressId = $addressId ? $addressId : 0 ;
            $verify = $this->getModelPaymentVerify()->loadExist($account->getId(), $addressId, 'offline');
            $this->_invoice_address = $verify->getInfo();
        }
        return $this->_invoice_address;
    }

    /**
     * get Base Url Media
     *
     * @return mixed
     */
    public function getBaseUrlMedia()
    {
        return $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);;
    }
}
