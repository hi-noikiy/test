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
namespace Magestore\Affiliateplus\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RequestPaymentActionMoneybooker
 * @package Magestore\Affiliateplus\Observer
 */
class AffiliateplusPaymentPrepare extends AbtractObserver implements ObserverInterface
{
    /**
     * @return \Magento\Customer\Model\Address
     */
    public function getModelCustomerAddress(){
        return $this->_objectManager->create('Magento\Customer\Model\Address');
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Payment\Verify
     */
    public function getModelPaymentVerify()
    {
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Verify');
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Payment
     */
    public function getModelPayment()
    {
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }

    /**
     * @return \Magestore\Affiliateplus\Helper\Payment
     */
    public function getHelperPayment()
    {
        return $this->_objectManager->get('Magestore\Affiliateplus\Helper\Payment');
    }
    /**
     * Set Final Price to product in product list
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isAffiliateModuleEnabled())
            return $this;

        $account = $this->_affiliateSession->getAccount();
        $object = $observer->getEvent()->getPaymentData();
        $files = $observer->getEvent()->getFile();
        $params = $object->getParams();
        if(isset($params['payment_method']) && $params['payment_method'] == 'offline')
        {
            if(isset($params['account_address_id']) && $params['account_address_id'])
            {

                $address = $this->getModelCustomerAddress()->load($params['account_address_id']);
                if ($address->getId()) {
                    $html = $address->format('html');
                    if(isset($files['invoice_address']) && is_array($files['invoice_address'])){
                        if(isset($files['invoice_address']['name']) && $files['invoice_address']['name'] != '') {
                            $verify = $this->getModelPaymentVerify()
                                ->loadExist($account->getId(), $address->getId(), 'offline');
                            if(!$verify->isVerified()){
                                $filename = $this->getHelperPayment()->uploadVerifyImage('invoice_address',$files);

                                if($filename){

                                    $verify->setData('info',$filename);
                                    $verify->setData('account_id',$account->getId());
                                    $verify->setData('payment_method','offline');
                                    $verify->setData('field',$address->getId());
                                    try{
                                        $verify->save();
                                        $params['invoice_address'] = $filename;
                                    }  catch (\Exception $e){

                                    }
                                }
                            }else{
                            }
                        }
                    }
                    $params['address_html'] = $html;
                }
            }else{
                $account_data = $params['account'];
                if(!$account_data) {
                    $account_data = $this->getModelCustomerAddress()->load($account->getAddressId());
                    if($account_data->getId())
                        $html = $account_data->format('html');
                }else {
                    $html =__('%1',$account->getName());
                    $address = $this->getModelCustomerAddress()->setData($account_data);
                    $html .= $address->format('html');
                }
                if(isset($files['invoice_address']) && is_array($files['invoice_address'])){
                    if(isset($files['invoice_address']['name']) && $files['invoice_address']['name'] != '') {
                        $filename = $this->getHelperPayment()->uploadVerifyImage('invoice_address', $files);

                        if($filename){
                            $verify = $this->getModelPaymentVerify();
                            $verify->setData('info',$filename);
                            $verify->setData('account_id',$account->getId());
                            $verify->setData('payment_method','offline');
                            $verify->setData('field',0);
                            try{
                                $verify->save();
                                $params['invoice_address'] = $filename;
                            }  catch (\Exception $e){

                            }
                        }
                    }
                }
                $params['address_html'] = $html;
            }

        }
        if(isset($params['payment_method']) && $params['payment_method'] == 'bank'){
            if (isset($params['payment_bankaccount_id']) && $params['payment_bankaccount_id']) {
                $bankAccount = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Bankaccount')
                    ->load($params['payment_bankaccount_id']);
                if ($bankAccount->getId()) {
                    $html = $bankAccount->format(true);
                    if(isset($files['bank_statement']) && is_array($files['bank_statement'])){
                        if(isset($files['bank_statement']['name']) && $files['bank_statement']['name'] != '') {
                            $verify = $this->getModelPaymentVerify()
                                ->loadExist($account->getId(), $bankAccount->getId(), 'bank');
                            if(!$verify->isVerified()){

                                $filename = $this->getHelperPayment()->uploadVerifyImage('bank_statement', $files);

                                if($filename){

                                    $verify->setData('info',$filename);
                                    $verify->setData('account_id',$account->getId());
                                    $verify->setData('payment_method','bank');
                                    $verify->setData('field',$bankAccount->getId());
                                    try{
                                        $verify->save();
                                        $params['bank_statement'] = $filename;
                                    }  catch (\Exception $e){

                                    }
                                }
                            }
                        }
                    }
                    $params['bankaccount_html'] = $html;
                }
            }else{
                $bank_account_data = $params['bank'];
                $html = __('Bank: %1',$bank_account_data['name']).'<br />';
                $html .= __('Account: %1',$bank_account_data['account_name']).'<br />';
                $html .= __('Acc Number: %1',$bank_account_data['account_number']).'<br />';
                if (isset($bank_account_data['routing_code'])){
                    $html .= __('Routing Code: %1',$bank_account_data['routing_code']).'<br />';
                }
                if (isset($bank_account_data['address'])){
                    $html .= __('Bank Address: %1',$bank_account_data['address']).'<br />';
                }
                if(isset($files['bank_statement']) && is_array($files['bank_statement'])){
                    if(isset($files['bank_statement']['name']) && $files['bank_statement']['name'] != '') {
                        $filename = $this->getHelperPayment()->uploadVerifyImage('bank_statement', $files);

                        if($filename){
                            $verify = $this->getModelPaymentVerify();
                            $verify->setData('info',$filename);
                            $verify->setData('account_id',$account->getId());
                            $verify->setData('payment_method','bank');
                            $verify->setData('field',0);
                            try{
                                $verify->save();
                                $params['bank_statement'] = $filename;
                            }  catch (\Exception $e){

                            }
                        }
                    }
                }
                $params['bankaccount_html'] = $html;
            }
        }
        $object->setParams($params);

    }
}