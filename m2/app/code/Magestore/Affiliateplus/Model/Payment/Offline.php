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
class Offline extends \Magestore\Affiliateplus\Model\Payment\AbstractPayment
{
    /**
     * @var string
     */
    protected $_code = 'offline';
    /**
     * @var string
     */
    protected $_formBlockType = 'Magestore\Affiliateplus\Block\Payment\Offline\Form';
    /**
     * @var string
     */
    protected $_infoBlockType = 'Magestore\Affiliateplus\Block\Payment\Offline\Info';
    /**
     * @var string
     */
    protected $_eventPrefix = 'affiliateplus_offline';
    /**
     * @var string
     */
    protected $_eventObject = 'affiliateplus_offline';
    /**
     *construct function
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment\Offline');
    }

    /**
     * @return $this
     */
    public function savePaymentMethodInfo(){
        $payment = $this->getPayment();
        if ($this->getOfflineAddressId()){
            $address = $this->_objectManager->create('Magestore\Customer\Model\Address')->load($this->getOfflineAddressId());
            $this->setAddressId($address->getId())
                ->setAddressHtml($address->format('html'));
        }
        $this->setTransferInfo($this->getOfflineTransferInfo())
            ->setMessage($this->getOfflineMessage());
        $this->setPaymentId($payment->getId())->save();
        return parent::savePaymentMethodInfo();
    }

    /**
     * @return $this
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
     * @return mixed
     */
    public function calculateFee(){
        return $this->getPayment()->getFee();
    }

    /**
     * @return mixed
     */
    public function getInfoString(){
        return __('Method: %1 \n',$this->getLabel());
    }

    /**
     * @return string
     */
    public function getInfoHtml(){
        $html = __('Method: ');
        $html .= '<strong>'.$this->getLabel().'</strong><br />';
        return $html;
    }

    public function afterSave(){
        $payment = $this->getPayment();
        if($payment->getStatus()== 3){
            if($payment->getPaymentMethod() == 'offline'){
                $verify = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Verify')
                    ->loadExist($payment->getAccountId(), $this->getAddressId(), 'offline');
                if(!$verify->isVerified()){
                    try{
                        $verify->setVerified(1)
                            ->save();
                    }  catch (\Exception $e){

                    }
                }
            }
        }elseif ($payment->getStatus() == 1) {
            if($payment->getPaymentMethod() == 'offline'){
                $verify = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Verify')
                    ->loadExist($payment->getAccountId(), 0, 'offline');
                if($verify->getId()){
                    try{
                        $verify->setData('field',$this->getAddressId())
                            ->save();

                    }  catch (\Exception $e){
                    }
                }
            }
        }
    }
}