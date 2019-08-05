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
namespace Magestore\Affiliateplus\Model\Payment;

/**
 * Class AbstractPayment
 * @package Magestore\Affiliateplus\Model\Payment
 */
class AbstractPayment extends \Magestore\Affiliateplus\Model\AbtractModel
{
    /**
     * @var string
     */
    protected $_code = '';
    /**
     * @var null
     */
    protected $_storeViewId = null;

    /**
     * @var
     */
    protected $_payment;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magestore\Affiliateplus\Block\Payment\Form';
    /**
     * @var string
     */
    protected $_infoBlockType = 'Magestore\Affiliateplus\Block\Payment\Info';

    /**
     * @param $value
     * @return $this
     */
    public function setPayment($value){
        $this->_payment = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayment(){
        return $this->_payment;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setStoreViewId($value){
        $this->_storeViewId = $value;
        return $this;
    }

    /**
     * @return null
     */
    public function getStoreViewId(){
        return $this->_storeViewId;
    }

    /**
     * @return string
     */
    public function getPaymentCode(){
        return $this->_code;
    }

    /**
     * @return string
     */
    public function getFormBlockType(){
        return $this->_formBlockType;
    }

    /**
     * @return string
     */
    public function getInfoBlockType(){
        return $this->_infoBlockType;
    }

    /**
     * @param $code
     * @return mixed
     */
    protected function _getPaymentConfig($code){
        return $this->_helperConfig->getConfig(\Magestore\Affiliateplus\Helper\Payment::XML_PAYMENT_METHODS.'/'.$this->getPaymentCode().'/'.$code,$this->getStoreId());
    }

    /**
     * @return mixed
     */
    public function isEnable(){
        return $this->_getPaymentConfig('active');
    }

    /**
     * @return int
     */
    public function calculateFee(){
        return 0;
    }


    /**
     * @return mixed
     */
    public function getLabel(){
        return $this->_getPaymentConfig('label');
    }

    /**
     * @return $this
     */
    public function loadPaymentMethodInfo(){
        return $this;
    }

    /**
     * @return $this
     */
    public function savePaymentMethodInfo(){
        return $this;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getInfoString(){
        return __('
			Method: %s \n
			Fee: %s \n
		',$this->getLabel(),$this->getFeePrice(false));
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getInfoHtml(){
        $html = __('Method: ');
        $html .= '<strong>'.$this->getLabel().'</strong><br />';
        $html .= __('Fee: ');
        $html .= '<strong>'.$this->getFeePrice(true).'</strong><br />';
        return $html;
    }

    /**
     * @return bool|mixed
     */
    public function getPaymentHelper() {
        if ($class = $this->_getPaymentConfig('helper')) {
            return $this->_objectManager->create($class);
        }
        return false;
    }
}