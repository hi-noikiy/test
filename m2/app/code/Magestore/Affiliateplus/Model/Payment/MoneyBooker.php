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
 * Class Credit
 * @package Magestore\Affiliateplus\Model\Payment
 */
class MoneyBooker extends \Magestore\Affiliateplus\Model\Payment\AbstractPayment
{
    /**
     * @var string
     */
    protected $_code = 'moneybooker';
    /**
     * @var string
     */
    protected $_formBlockType = 'Magestore\Affiliateplus\Block\Payment\Moneybooker';



    /**
     *
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment\MoneyBooker');
    }

    /**
     * @return mixed
     */
    public function calculateFee(){
        return $this->getPayment()->getFee();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getInfoString(){
        $info = __('
			Method: %s \n
			Email: %s \n'
            ,$this->getLabel()
            ,$this->getEmail()
        );
        if ($this->getTransactionId()) {
            return $info . __('Transaction Id: %s \n', $this->getTransactionId());
        }
        return $info;
    }

    /**
     * @return string
     */
    public function getInfoHtml(){
        if(!$this->getId()){
            $payment = $this->_objectManager->get('Magento\Framework\Registry')->registry('confirm_payment_data');
            if($payment)
                $this->setData($payment->getData());
        }
        $html = __('Method: ');
        $html .= '<strong>'.$this->getLabel().'</strong><br />';
        $html .= __('Email: ');
        $html .= '<strong>'.$this->getEmail().'</strong><br />';
        if($this->getId() && $this->getTransactionId()){
            $html .= __('Transaction Id: ');
            $html .= '<strong>'.$this->getTransactionId().'</strong><br />';
        }
        return $html;
    }

    /**
     * load information of moneybooker payment method
     *
     * @return Magestore\Affiliateplus\Model|Payment\Moneybooker
     */
    public function loadPaymentMethodInfo(){
        if ($this->getPayment()){
            $paymentInfo = $this->getCollection()
                ->addFieldToFilter('payment_id',$this->getPayment()->getId())
                ->getFirstItem();
            if ($paymentInfo)
                $this->addData($paymentInfo->getData())->setId($paymentInfo->getId());
        }
        return parent::loadPaymentMethodInfo();
    }

    /**
     * Save Payment Method Information
     *
     * @return Magestore\Affiliateplus\Model\Payment\Abstract
     */
    public function savePaymentMethodInfo(){
        $this->setPaymentId($this->getPayment()->getId())->save();
        return parent::savePaymentMethodInfo();
    }

    /**
     * @param $amount
     * @param $payer
     * @return float|int
     */
    public function getEstimateFee ($amount, $payer){
        $amount = floatval($amount);
        $fee = 0;
        if($payer == 'recipient'){
            $fee = floatval($amount*0.01/1.01);
        }  else {
            $fee = $amount*0.01;
        }
        return $fee;
    }

    /**
     * @return mixed
     */
    public function beforeSave(){
        if ($this->getData('moneybooker_email')) {
            $this->setData('email', $this->getData('moneybooker_email'));
        }
        if ($this->getData('moneybooker_transaction_id')) {
            $this->setData('transaction_id', $this->getData('moneybooker_transaction_id'));
        }
        return parent::beforeSave();
    }
}