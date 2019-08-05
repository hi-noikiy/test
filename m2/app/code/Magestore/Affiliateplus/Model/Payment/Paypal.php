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
class Paypal extends \Magestore\Affiliateplus\Model\Payment\AbstractPayment
{
    /**
     * @var string
     */
    protected $_code = 'paypal';
    /**
     * @var string
     */
    protected $_formBlockType = 'Magestore\Affiliateplus\Block\Payment\Paypal';



    /**
     *
     */
    public function _construct() {
        parent::_construct();
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment\Paypal');
    }

    /**
     * @return mixed
     */
    public function calculateFee() {
        return $this->getPayment()->getFee();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getInfoString() {
        $info = __('
			Method: %1 \n
			Email: %1 \n'
            , $this->getLabel()
            , $this->getEmail()
        );
        if ($this->getTransactionId()) {
            return $info . __('Transaction Id: %s \n', $this->getTransactionId());
        }
        return $info;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getInfoHtml() {
        if (!$this->getId()) {
            $payment = $this->_objectManager->get('Magento\Framework\Registry')->registry('confirm_payment_data');

            if ($payment){
                $this->setData($payment->getData());
                if(!$this->getEmail())
                    $this->setEmail($payment->getPaypalEmail());
            }
        }

        /* End edit */
        $html = __('Method: ');
        $html .= '<strong>' . $this->getLabel() . '</strong><br />';
        $html .= __('Email: ');
        $html .= '<strong>' . $this->getEmail() . '</strong><br />';
        if ($this->getId() && $this->getTransactionId()) {
            $html .= __('Transaction Id: ');
            $html .= '<strong>' . $this->getTransactionId() . '</strong><br />';
        }
        return $html;
    }

    /**
     * @return $this
     */
    public function loadPaymentMethodInfo() {
        if ($this->getPayment()) {
            $paymentInfo = $this->getCollection()
                ->addFieldToFilter('payment_id', $this->getPayment()->getId())
                ->getFirstItem();
            if ($paymentInfo)
                $this->addData($paymentInfo->getData())->setId($paymentInfo->getId());
        }
        return parent::loadPaymentMethodInfo();
    }

    /**
     * @return $this
     */
    public function savePaymentMethodInfo() {
        $this->setPaymentId($this->getPayment()->getId())->save();
        return parent::savePaymentMethodInfo();
    }

    /**
     * @param $requiredAmount
     * @param $payer
     * @return float|int
     */
    public function getEstimateFee($requiredAmount, $payer) {
        if ($payer=='recipient')
            $amount = round($requiredAmount, 2);
        else {
            if ($requiredAmount >= 50)
                $amount = round($requiredAmount - 1, 2); // max fee is 1$ by api
            else
                $amount = round($requiredAmount / 1.02, 2); // fees 2% when payment by api
        }

        if ($amount >= 50)
            $fee = 1;
        else
            $fee = round($amount * 0.02, 2);
        return $fee;
    }
}