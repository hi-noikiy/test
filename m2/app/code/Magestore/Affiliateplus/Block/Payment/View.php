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
class View extends \Magestore\Affiliateplus\Block\Payment\Form
{
    protected $_payment = '';
    protected $_collection = '';
    /**
     * @return mixed
     */
    public function getAccount(){
        return $this->_sessionModel->getAccount();
    }

    /**
     * @return mixed
     */
    public function getPayment(){
        if (!$this->_payment){
            $payment = $this->_registry->registry('view_payment_data');
            $payment->addPaymentInfo();
            $this->_payment = $payment;
        }
        return $this->_payment;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod(){
        return $this->getPayment()->getPayment();
    }

    /**
     * @return array
     */
    public function getStatusArray(){
        return [
            1	=> __('Pending'),
            2	=> __('Processing'),
            3	=> __('Complete'),
            4	=> __('Canceled'),
        ];
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        if ($this->getPaymentMethod())
            if ($paymentMethodInfoBlock = $this->getLayout()->createBlock($this->getPaymentMethod()->getInfoBlockType(),'payment_method_info')){
                $paymentMethodInfoBlock->setPaymentMethod($this->getPaymentMethod());
                $this->setChild('payment_method_info',$paymentMethodInfoBlock);
            }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFullHistory() {
        if (!$this->_collection) {
            $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Payment\History\Collection')
                ->addFieldToFilter('payment_id', $this->getPayment()->getId());
            $collection->getSelect()->order('created_time DESC');
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return mixed
     */
    public function getCollection() {
        return $this->getFullHistory();
    }
    /**
     * @param null $date
     * @param int $format
     * @param bool|false $showTime
     * @param null $timezone
     * @return string
     */



}
