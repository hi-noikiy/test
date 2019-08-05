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
class Info extends \Magestore\Affiliateplus\Block\AbstractTemplate
{

    protected $_payment_method;

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
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::payment/info.phtml');
        return $this;
    }

    /**
     * @return null
     */
    public function getPayment(){
        if ($this->getPaymentMethod())
            return $this->getPaymentMethod()->getPayment();
        return null;
    }
}
