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
 * Class Confirm
 * @package Magestore\Affiliateplus\Block\Payment
 */
class Confirm extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @var
     */
    protected $_payment = '';
    /**
     * @return mixed
     */
    public function getAccount(){
        return $this->_sessionModel->getAccount();
    }

    /**
     * @return mixed
     */
    public function getRegistry()
    {
        return $this->_objectManager->get('Magento\Framework\Registry');
    }
    /**
     * @return mixed
     */
    public function getPayment(){
        if (!$this->hasData('payment')){
            $payment = $this->getRegistry()->registry('confirm_payment_data');
            $payment->addPaymentInfo();
            $payment->applyTax();
            $this->setData('payment',$payment);
        }
        return $this->getData('payment');
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
        return array(
            1	=> __('Pending'),
            2	=> __('Processing'),
            3	=> __('Complete'),
            4   => __('Canceled')
        );
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout(){
        parent::_prepareLayout();

        if ($this->getPaymentMethod())
            if ($paymentMethodInfoBlock = $this->getLayout()->createBlock($this->getPaymentMethod()->getInfoBlockType(),'payment_method_info')){
                if(!$this->getPaymentMethod()->getId()){
                    $this->getPaymentMethod()->setData($this->getPayment()->getData());
                }
                $paymentMethodInfoBlock->setPaymentMethod($this->getPaymentMethod());
                $this->setChild('payment_method_info',$paymentMethodInfoBlock);
            }

        return $this;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Data
     */
    public function getHelper(){
        return $this->_dataHelper;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->getRequest()->getFiles();
    }


}
