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
class RequestPaymentActionBank extends AbtractObserver implements ObserverInterface
{
    /**
     * Set Final Price to product in product list
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isAffiliateModuleEnabled())
            return $this;
        $paymentObj = $observer->getEvent()->getPaymentObj();
        $payment = $observer->getEvent()->getPayment();
        $paymentMethod = $observer->getEvent()->getPaymentMethod();
        $request = $observer->getEvent()->getRequest();
        $account = $this->_affiliateSession->getAccount();

        if (isset($data['payment_bankaccount_id']) && $data['payment_bankaccount_id']) {
            $bankAccount = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Bankaccount')
                ->load($data['payment_bankaccount_id']);
        } else {
            $bank_account_data = $request->getPost('bank');
            $bankAccount = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Bankaccount')
                ->setId(null)
                 ->setData($bank_account_data)
                ->setAccountId($account->getId());
            $errors = $bankAccount->validate();
            if (!is_array($errors)){
                $errors = array();
            }
            $validationResult = (count($errors) == 0);
            try {
                if (true === $validationResult) {
                    $bankAccount->save();
                } else {
                    foreach ($errors as $error)
                        $this->messageManager->addError($error);
                    return $this;
                }
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                $this->messageManager->addError($e->getMessage());
                return $this;
            }
        }
        if ($bankAccount->getId()) {
            $paymentMethod->setBankaccountId($bankAccount->getId())
                ->setBankaccountHtml($bankAccount->format(true));
            $paymentObj->setRequired(false);
            $fee = $this->_getConfigHelper()->getConfig('affiliateplus_payment/bank/fee_value');
            if ($this->_getConfigHelper()->getConfig('affiliateplus_payment/bank/fee_type') == 'percentage')
                $fee = $payment->getAmount() * $fee / 100;
            $payment->setFee($fee);
        }
        return $this;

    }


}