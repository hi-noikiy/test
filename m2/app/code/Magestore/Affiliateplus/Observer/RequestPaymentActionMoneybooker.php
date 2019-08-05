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
class RequestPaymentActionMoneybooker extends AbtractObserver implements ObserverInterface
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
        $account = $this->getAffiliateSession()->getAccount();
        $storeId = $this->_storeManager->getStore()->getId();

        $moneybooker_email = $request->getParam('moneybooker_email');
        if(!$moneybooker_email)
            if($this->_affiliateSession->getPayment())
                $moneybooker_email = $this->getAffiliateSession()->getPayment()->getEmail();

        if ($moneybooker_email && $moneybooker_email != $account->getMoneybookerEmail()) {
            $accountModel = $this->_objectManager->create('Magestore\Affiliateplus\Model\Account')
                ->setStoreId($storeId)
                ->load($account->getId())
            ;
            try {
                $accountModel->setMoneybookerEmail($moneybooker_email)
                    ->setId($account->getId())
                    ->save();
            } catch (\Exception $e) {

            }
        }
        $moneybooker_email = $moneybooker_email ? $moneybooker_email : $account->getMoneybookerEmail();
        if ($moneybooker_email) {
            $paymentMethod->setEmail($moneybooker_email);
            $paymentObj->setRequired(false);
        }

    }

    /**
     * @return \Magestore\Affiliateplus\Model\Session
     */
    public function getAffiliateSession()
    {
        return $this->_affiliateSession;
    }
}