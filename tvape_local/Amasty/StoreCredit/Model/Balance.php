<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Balance extends Mage_Core_Model_Abstract
{


    protected function _construct()
    {
        $this->_init('amstcred/balance');
    }


    public function loadByCustomer()
    {
        $this->_prepareCustomer();
        if ($this->hasWebsiteId()) {
            $websiteId = $this->getWebsiteId();
        } else {
            if (Mage::app()->getStore()->isAdmin()) {
                Mage::throwException(Mage::helper('amstcred')->__('Website ID must be set.'));
            }
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }
        $this->getResource()->loadByCustomerAndWebsite($this, $this->getCustomerId(), $websiteId);

        return $this;
    }


    protected function _beforeSave()
    {

        $this->_prepareCustomer();
        if (!$this->hasWebsiteId()) {
            $this->setWebsiteId($this->getCustomer()->getWebsiteId());
        }

        if (!$this->getId()) {
            $this->loadByCustomer();
        }

        $this->_prepareAmountDelta();


        return parent::_beforeSave();
    }


    protected function _afterSave()
    {
        parent::_afterSave();

        // save history action
        if ($this->getAmountDelta()) {
            $history = Mage::getModel('amstcred/balanceHistory')
                ->setBalanceModel($this);

            if ($this->getSubscribeUpdates() && !$this->getNotNotified()) {
                $this->_notify(array('operation_name' => $history->getOperationName()));
                $history->setIsNotified(1);
            }
            $history->save();

        }

        return $this;
    }

    protected function _prepareCustomer()
    {
        if ($this->getCustomer() && $this->getCustomer()->getId()) {
            $this->setCustomerId($this->getCustomer()->getId());
        }
        if (!$this->getCustomerId()) {
            Mage::throwException(Mage::helper('amstcred')->__('Customer ID must be specified.'));
        }
        if (!$this->getCustomer()) {
            $this->setCustomer(Mage::getModel('customer/customer')->load($this->getCustomerId()));
        }
        if (!$this->getCustomer()->getId()) {
            Mage::throwException(Mage::helper('amstcred')->__('Customer is not set or does not exist.'));
        }
    }


    protected function _prepareAmountDelta()
    {
        $result = 0;
        if ($this->getAmountDelta()) {
            $result = (float)$this->getAmountDelta();
            if ($this->getId()) {
                $currentAmount = $this->getAmount();
                if (($result < 0) && (($currentAmount + $result) < 0)) {
                    $result = -$currentAmount;
                }
            } elseif ($result <= 0) {
                $result = 0;
            }
        }
        $this->setAmountDelta($result);
        if (!$this->getId()) {
            $this->setAmount($result);
        } else {
            $this->setAmount($this->getAmount() + $result);
        }
        return $result;
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        $customer = parent::getCustomer();

        $websiteId = $this->getWebsiteId();
        if (!$websiteId) {
            $websiteId = Mage::app()->getWebsite()->getId();
        }

        if (!$customer && $customer_id = $this->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId)->load($customer_id);
            parent::setCustomer($customer);
        }

        return $customer;
    }


    protected function _notify($data)
    {
        $emailModel = Mage::getModel('core/email_template');

        $recipient_email = $this->getCustomer()->getEmail();
        $recipient_name = $this->getCustomer()->getName();

        $storeId = null;
        if ($this->getData('store_id')) {
            $storeId = $this->getData('store_id');
        }
        $storeId = Mage::app()->getStore($storeId)->getId();
        $template = Mage::getStoreConfig('amstcred/email/email_template_notify', $storeId);

        $emailModel->sendTransactional(
            $template,
            Mage::getStoreConfig('amstcred/email/email_identity', $storeId),
            $recipient_email,
            null,
            array(
                'recipient_name' => $recipient_name,
                'amount_delta' => Mage::helper('core')->currencyByStore($this->getAmountDelta(), $storeId),
                'operation_name' => $data['operation_name'],
                'current_balance' => Mage::helper('core')->currencyByStore($this->getAmount(), $storeId),
            ),
            $storeId
        );
    }

}
