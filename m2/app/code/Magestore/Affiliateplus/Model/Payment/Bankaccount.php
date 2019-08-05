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
class Bankaccount extends \Magestore\Affiliateplus\Model\Payment\AbstractPayment
{

    public function _construct() {
        parent::_construct();
        $this->_init('Magestore\Affiliateplus\Model\ResourceModel\Payment\Bankaccount');
    }

    /**
     * @param bool|true $isHtml
     * @return string
     */
    public function format($isHtml = true){
        if ($isHtml){
            $html = __('Bank: %1',$this->getName()).'<br />';
            $html .= __('Account: %1',$this->getAccountName()).'<br />';
            $html .= __('Acc Number: %1',$this->getAccountNumber()).'<br />';
            if ($this->getRoutingCode())
                $html .= __('Routing Code: %1',$this->getRoutingCode()).'<br />';
            if ($this->getSwiftCode())
                $html .= __('SWIFT Code: %1',$this->getSwiftCode()).'<br />';
            if ($this->getAddress())
                $html .= __('Bank Address: %1',$this->getAddress()).'<br />';
            return $html;
        }
        return $this->getAccountName().', '. $this->getAccountNumber().', '. $this->getName();
    }

    /**
     * get Bank Statement
     */
    public function getBankStatement(){
        if($this->getId() ){
            $verify = $this->getModel('Magestore\Affiliateplus\Model\Payment\Verify')
                ->loadExist($this->getAccountId(), $this->getId(), 'bank');
            return $verify->getInfo();
        }
        return ;
    }

    /**
     * @return mixed
     */
    public function getAccount(){
        return $this->getModel('Magestore\Affiliateplus\Model\Session')->getAccount();
    }

    /**
     * @param $account
     * @return $this|null
     */
    public function getBankAccounts($account){
        if (!$account) {
            return null;
        }
        $collection = $this->getCollection()
            ->addFieldToFilter('account_id',$account->getId());
        return $collection;
    }

    /**
     * @return array|bool
     */
    public function validate(){
        $errors = array();

        if (!$this->getName())
            $errors[] = __('Bank name is empty.');
        if (!$this->getAccountName())
            $errors[] = __('Bank account name is empty.');
        if (!$this->getAccountNumber())
            $errors[] = __('Bank account number is empty.');

        if (count($errors) == 0)
            return false;
        return $errors;
    }
}