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
use Magestore\Affiliateplus\Model\Transaction;

/**
 * Class ProductGetFinalPrice
 * @package Magestore\Affiliateplus\Observer
 */
class CustomerSaveAfter extends AbtractObserver implements ObserverInterface
{
    /**
     * Set Final Price to product in product list
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }

        $request = $this->_request;
        $customer = $observer['customer'];
        $account = $this->_accountFactory->create()->loadByCustomer($customer);
        if ($account->getId() > 0) {
            $account->setName($customer->getName());
            $account->setEmail($customer->getEmail());
            $account->save();
        }elseif ($this->_helperConfig->getAccountConfig('auto_create_affiliate') && ($request->getActionName() != 'createPost') && ($request->getModuleName() != 'affiliates') && !$this->_helper->isAdmin()) {
            try {
                $this->_helperAccount->createAffiliateAccount('', '', $customer, $request->getPost('notification'), '', '');
            } catch (\Exception $e) {
                return $this;
            }
        } elseif ($this->_helperConfig->getAccountConfig('auto_create_affiliate') && ($request->getActionName() != 'createPost') && ($request->getModuleName() != 'affiliateplusadmin') && $this->_helper->isAdmin()) { //check if this is affiliate create form or not
            try {
                $this->_helperAccount->createAffiliateAccount('', '', $customer, $request->getPost('notification'), '', '');
            } catch (\Exception $e) {
                return $this;
            }
        }
        return $this;
    }
}