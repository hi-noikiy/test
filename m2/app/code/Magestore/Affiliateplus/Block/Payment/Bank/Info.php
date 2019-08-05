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
namespace Magestore\Affiliateplus\Block\Payment\Bank;


/**
 * Class Request
 * @package Magestore\Affiliateplus\Block\Payment
 */
class Info extends \Magestore\Affiliateplus\Block\Payment\Form
{
    /**
     * @var
     */
    protected $_countryCollection;
    /**
     * @var
     */
    protected $_bank_statement;
    /**
     * @return $this
     */
    public function _prepareLayout(){
        parent::_prepareLayout();
        $this->setTemplate('Magestore_Affiliateplus::payment/bank/info.phtml');
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBankStatement(){
        if(!$this->_bank_statement){
            $payment = $this->getPaymentMethod();
            $account = $this->_sessionModel->getAccount();
            $bankaccountId = $payment->getPaymentBankaccountId() ? $payment->getPaymentBankaccountId() : $payment->getBankaccountId();
            $bankaccountId = $bankaccountId ? $bankaccountId : 0;
            $verify = $this->getModelPaymentVerify()->loadExist($account->getId(), $bankaccountId, 'bank');

            $this->_bank_statement = $verify->getInfo();
        }
        return $this->_bank_statement;
    }


}
