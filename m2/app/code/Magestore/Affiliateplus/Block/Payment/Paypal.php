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
class Paypal extends \Magestore\Affiliateplus\Block\Payment\Form
{
    /**
     * @return $this
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::payment/paypal.phtml');
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAcount(){
        return $this->_sessionModel->getAccount();
    }

    /**
     * @param $accountId
     * @param $email
     * @return bool
     */
    public function isVerified($accountId, $email){

        $verifyCollection = $this->getModelPaymentVerify()
            ->getCollection()
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('payment_method', 'paypal')
            ->addFieldToFilter('field', $email)
            ->addFieldToFilter('verified', '1')
        ;
        if($verifyCollection->getSize())
            return true;
        return false;
    }



}
