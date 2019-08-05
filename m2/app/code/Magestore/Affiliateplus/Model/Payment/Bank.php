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
class Bank extends \Magestore\Affiliateplus\Model\Payment\AbstractPayment
{
    /**
     * @var \Magestore\Affiliateplus\Block\Payment\Bank\Form
     */
    protected $_formBlockType = 'Magestore\Affiliateplus\Block\Payment\Bank\Form';
    /**
     * @var \Magestore\Affiliateplus\Block\Payment\Bank\Info
     */
    protected $_infoBlockType = 'Magestore\Affiliateplus\Block\Payment\Bank\Info';

    /**
     * @var string
     */
    protected $_eventPrefix = 'payment_bank';
    /**
     * @var string
     */
    protected $_eventObject = 'payment_bank';
    /**
     * @var string
     */
    protected $_code = 'bank';
    /**
     * constuct function
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment\Bank');
    }

    /**
     * @return $this
     */
    public function savePaymentMethodInfo(){
        $payment = $this->getPayment();
        if ($this->getBankBankaccountId()){
            $bankAccount = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Bankaccount')->load($this->getBankBankaccountId());
            $this->setBankaccountId($bankAccount->getId())
                ->setBankaccountHtml($bankAccount->format(true));
        }
        $this->setInvoiceNumber($this->getBankInvoiceNumber())
            ->setMessage($this->getBankMessage());
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

    /**
     * afterSave
     */
    public function afterSave(){
        $payment = $this->getPayment();
        if($payment->getStatus()== 3){
            if($payment->getPaymentMethod() == 'bank'){
                $verify = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Verify')
                    ->loadExist($payment->getAccountId(), $this->getBankaccountId(), 'bank');
                if(!$verify->isVerified()){
                    try{
                        $verify->setVerified(1)
                            ->save();
                    }  catch (\Exception $e){

                    }
                }
            }
        }elseif ($payment->getStatus() == 1) {
            if($payment->getPaymentMethod() == 'bank'){
                $verify = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Verify')
                    ->loadExist($payment->getAccountId(), 0, 'bank');
                if($verify->getId()){
                    try{
                        $verify->setData('field',$this->getBankaccountId())
                            ->save();

                    }  catch (\Exception $e){
                    }
                }
            }
        }
    }

}