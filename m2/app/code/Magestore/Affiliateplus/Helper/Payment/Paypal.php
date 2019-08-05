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
namespace Magestore\Affiliateplus\Helper\Payment;
/**
 * Class Paypal
 * @package Magestore\Affiliateplus\Helper\Payment
 */
class Paypal extends \Magestore\Affiliateplus\Helper\HelperAbstract
{
    /**
     * @param $account
     * @param $amount
     * @param null $storeId
     * @param null $paymentId
     */
    public function payoutByApi($account, $amount, $storeId = null, $paymentId = null) {
        if ($account->getStatus() == 2)
            return;
        if (!$storeId) {
            $stores = $this->_storeManager->getStores();
            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        } else {
            $storeIds = $storeId;
        }
        $payment = $this->_getPaymentModel()
            ->load($paymentId)
            ->setId($paymentId)
            ->setPaymentMethod('paypal')
            ->setAmount($amount)
            ->setAccountId($account->getId())
            ->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setStoreIds(implode(',', $storeIds))
            ->setIsPayerFee(0);
        if ($account->getData('is_created_by_recurring')) {
            $payment->setData('is_created_by_recurring', 1)
                ->setData('is_recurring', 1);
        }
        $now = new \DateTime();
        if (!$paymentId) {
            $payment->setRequestTime($now)
                ->setStatus(1)
                ->setIsRequest(0);
        }
        if ($this->getConfig('affiliateplus/payment/who_pay_fees',$storeId) == 'payer')
            $payment->setIsPayerFee(1);

        $requiredAmount = $payment->getAmount();
        if ($payment->getIsPayerFee()){
            $amount = round($requiredAmount, 2);
        }else {
            if ($requiredAmount >= 50){
                $amount = round($requiredAmount - 1, 2); // max fee is 1$ by api
            }else{
                $amount = round($requiredAmount / 1.02, 2); // fees 2% when payment by api
            }
        }

        if ($amount >= 50){
            $fee = 1;
        }else{
            $fee = round($amount * 0.02, 2);
        }

        $data = [
            [
                'amount' => $amount,
                'email' => $account->getPaypalEmail()
            ]
        ];

        $url = $this->getPaymanetUrl($data);
        $http =  new \Magento\Framework\HTTP\Adapter\Curl();
//        $http->setConfig([['header' => false]]);
        $http->write('GET', $url, '1.1');
        $response = $http->read();
        $pos = strpos($response, 'ACK=Success');
        $payment->setData('affiliateplus_account', $account);
        if ($pos) {
            try {
                $payment->setPaymentMethod('paypal')
                    ->setFee($fee)
                    ->setStatus(3) //complete
                    ->save();

                $paypalPayment = $payment->getPayment()
                    ->setEmail($account->getPaypalEmail())
                    ->savePaymentMethodInfo();

            } catch (\Exception $e) {
            }
        } else {
            $payment->save();
            $paypalPayment = $payment->getPayment()
                ->setEmail($account->getPaypalEmail())
                ->savePaymentMethodInfo();
            $account->save();
        }
        return $payment;
    }

    /**
     * data is list email and value of payment
     * @param $data
     * @return string
     */
    public function getPaymanetUrl($data){
        $url = $this->_getMasspayUrl();
        $i = 0;
        $baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
        foreach($data as $item){
            $url .= '&L_EMAIL'.$i.'='.$item['email'].'&L_AMT'.$i.'='.$item['amount'].'&CURRENCYCODE'.$i.'='.$baseCurrencyCode;
            $i++;
        }
        return $url;
    }

    /**
     * @return string
     */
    protected function _getMasspayUrl(){
        $url = $this->_getApiEndpoint();
        $url .= '&METHOD=MassPay&RECEIVERTYPE=EmailAddress';
        return $url;
    }

    /**
     * @return string
     */
    protected function _getApiEndpoint(){
        if($this->getConfig('affiliateplus_payment/paypal/user_mechant_email_default')){
            $isSandbox = $this->getConfig('paypal/wpp/sandbox_flag');
        } else {
            $isSandbox = $this->getConfig('affiliateplus_payment/paypal/sandbox_mode');
        }
        $paypalApi = $this->_getPaypalApi();
        $url = sprintf('https://api-3t%s.paypal.com/nvp?', $isSandbox ? '.sandbox' : '');
        $url .= 'USER=' . $paypalApi['api_username'] . '&PWD=' . $paypalApi['api_password'] . '&SIGNATURE=' . $paypalApi['api_signature'] . '&VERSION=62.5';
        return $url;
    }

    /**
     * @return mixed
     */
    protected function _getPaypalApi(){
        if($this->getConfig('affiliateplus_payment/paypal/user_mechant_email_default')) {
            $data['api_username'] = $this->getConfig('paypal/wpp/api_username');
            $data['api_password'] = $this->getConfig('paypal/wpp/api_password');
            $data['api_signature'] = $this->getConfig('paypal/wpp/api_signature');
        }else {
            $data['api_username'] = $this->getConfig('affiliateplus_payment/paypal/api_username');
            $data['api_password'] = $this->getConfig('affiliateplus_payment/paypal/api_password');
            $data['api_signature'] = $this->getConfig('affiliateplus_payment/paypal/api_signature');
        }
        return $data;
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Payment
     */
    protected function _getPaymentModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }
}