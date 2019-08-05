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
class RequestPaymentActionOffline extends AbtractObserver implements ObserverInterface
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

        $customer = $this->_objectManager->create('Magento\Customer\Model\Session')->getCustomer();
        $data = $request->getPost();
        if ($data['account_address_id']) {
            $address = $this->_objectManager->create('Magento\Customer\Model\Address')
                ->load($data['account_address_id']);
        } else {
            $address_data = $request->getPost('account');
            $address = $this->_objectManager->create('Magento\Customer\Model\Address')
                ->setData($address_data)
                ->setParentId($customer->getId())
                ->setFirstname($customer->getFirstname())
                ->setLastname($customer->getLastname())
                ->setId(null);
            $customer->addAddress($address);
            $errors = $address->validate();
            if (!is_array($errors))
                $errors = [];
            $validationResult = (count($errors) == 0);
            try {
                if (true === $validationResult) {
                    $address->save();
                } else {
                    foreach ($errors as $error)
                        $this->_objectManager->get('Magento\Framework\Session\SessionManager')->addError($error);
                    return $this;
                }
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Framework\Session\SessionManager')->addError($e->getMessage());
                return $this;
            }
        }
        if ($address->getId()) {
            $paymentMethod->setAddressId($address->getId());
            $paymentMethod->setAddressHtml($address->format('html'));
            $paymentObj->setRequired(false);
            $fee = $this->_getConfigHelper()->getConfig('affiliateplus_payment/offline/fee_value');
            if ($this->_getConfigHelper()->getConfig('affiliateplus_payment/offline/fee_type') == 'percentage')
                $fee = $payment->getAmount() * $fee / 100;
            $payment->setFee($fee);
        }
        return $this;

    }

}