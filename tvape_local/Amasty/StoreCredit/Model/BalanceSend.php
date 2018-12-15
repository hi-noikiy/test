<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_BalanceSend extends Mage_Core_Model_Abstract
{

    public function send()
    {
        $customerBalance = $this->_validate();
        $this->setWebsiteId($this->getSender()->getWebsiteId());
        $this->setSenderId($this->getSender()->getId());

        $transaction = Mage::getModel('core/resource_transaction');
        $recipient = Mage::getModel('customer/customer')->setWebsiteId($this->getWebsiteId())->loadByEmail($this->getRecipientEmail());

        if ($recipient->getId() == $this->getSender()->getId()) {
            Mage::throwException(Mage::helper('amstcred')->__('Incorrect recipient!'));
        }


        $customerBalance
            ->setAmountDelta(-$this->getAmount())
            ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_USER_SEND)
            ->setActionData($this->getRecipientName());

        $transaction->addObject($customerBalance);
        if ($recipient->getId() && $recipient->getWebsiteId() == $this->getSender()->getWebsiteId()) {
            /**
             * @var Amasty_StoreCredit_Model_Balance $recipientBalance
             */
            $recipientBalance = Mage::getModel('amstcred/balance')->setCustomer($recipient)->loadByCustomer();
            $recipientBalance->setAmountDelta($this->getAmount())
                ->setAction(Amasty_StoreCredit_Model_BalanceHistory::ACTION_USER)
                ->setActionData($this->getSender()->getName())
                ->setNotNotified(1);

            $transaction->addObject($recipientBalance);

            $this->setRecipientId($recipient->getId());
            $this->setIsRedeemed(1);
        }

        $transaction->addObject($this);

        $transaction->save();
        $this->_sendToEmail();
    }


    /**
     * @param $email
     * @param $websiteId
     *
     * @return $this
     */
    public function loadByEmailAndWebsite($email, $websiteId)
    {
        return $this->getCollection()
            ->addFieldToFilter('recipient_email', array('eq' => $email))
            ->addFieldToFilter('website_id', array('eq' => $websiteId))
            ->addFieldToFilter('is_redeemed', array('eq' => 0))->getFirstItem();

    }

    protected function _construct()
    {
        $this->_init('amstcred/balanceSend');
    }

    protected function _beforeSave()
    {
        $this->setCreatedAt(Mage::getModel('core/date')->gmtDate());
        return parent::_beforeSave();
    }

    /**
     * @return Amasty_StoreCredit_Model_Balance
     * @throws Mage_Core_Exception
     */
    protected function _validate()
    {
        $requiredFields = array(
            'recipient_name' => Mage::helper('amstcred')->__('Recipient Name'),
            'recipient_email' => Mage::helper('amstcred')->__('Recipient Email'),
            'amount' => Mage::helper('amstcred')->__('Amount'),
        );
        foreach ($requiredFields as $requiredField => $requiredFieldTitle) {
            $requiredFieldValue = $this->getData($requiredField);
            if (empty($requiredFieldValue)) {
                Mage::throwException(Mage::helper('amstcred')->__('Field %s is required'), $requiredFieldTitle);
            }
        }
        /**
         * @var Amasty_StoreCredit_Model_Balance $customerBalance
         */
        $customerBalance = Mage::getModel('amstcred/balance')->setCustomer($this->getSender())->loadByCustomer();

        $this->setAmount((float)$this->getAmount());

        if ($this->getAmount() < 0.0001) {
            Mage::throwException(Mage::helper('amstcred')->__('Incorrect Amount'));
        }

        if ($customerBalance->getAmount() < $this->getAmount()) {
            Mage::throwException(Mage::helper('amstcred')->__('Insufficient Funds'));
        }


        return $customerBalance;
    }


    protected function _sendToEmail()
    {
        $emailModel = Mage::getModel('core/email_template');

        $storeId = null;
        if ($this->getData('store_id')) {
            $storeId = $this->getData('store_id');
        }
        $storeId = Mage::app()->getStore($storeId)->getId();
        $template = Mage::getStoreConfig('amstcred/email/email_template_send_balance', $storeId);

        $emailModel->sendTransactional(
            $template,
            Mage::getStoreConfig('amstcred/email/email_identity', $storeId),
            $this->getRecipientEmail(),
            null,
            array(
                'recipient_name' => $this->getRecipientName(),
                'amount' => Mage::helper('core')->currencyByStore($this->getAmount(), $storeId),
                'sender_name' => $this->getSender()->getName(),
                'message' => $this->getMessage(),
                'login_link' => Mage::helper('customer')->getLoginUrl(),
                'register_link' => Mage::helper('customer')->getRegisterUrl(),
            ),
            $storeId
        );
    }


}
