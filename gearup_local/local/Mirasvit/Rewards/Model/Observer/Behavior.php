<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Model_Observer_Behavior
{
    /**
     * @var bool $_customerNew
     */
    protected $_customerNew;

    /**
     * @param Varien_Object $observer
     * @return Mirasvit_Rewards_Model_Observer_Behavior
     */
    public function customerBeforeSave($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($customer->isObjectNew()) {
            $this->_customerNew = true;
        }

        return $this;
    }

    /**
     * @param Varien_Object $observer
     * @return Mirasvit_Rewards_Model_Observer_Behavior
     */
    public function customerLogin($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($customer && !$this->_customerNew) {
            Mage::helper('rewards/behavior')->processRule(
                Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_LOGIN, $customer);
        }

        return $this;
    }

    /**
     * Awards points for sign-in
     * @param Varien_Object $observer
     * @return Mirasvit_Rewards_Model_Observer_Behavior
     */
    public function customerAfterCommit($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $origData = $customer->getOrigData();
        if (($customer && $this->_customerNew) ||
            (isset($origData['confirmation']) && !$customer->getData('confirmation')) ) {
            Mage::getSingleton('rewards/observer_referral')->customerAfterCreate($customer);
            Mage::helper('rewards/behavior')->processRule(
                Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_SIGNUP, $customer);
            if ($customer->getIsSubscribed()) {
                Mage::helper('rewards/behavior')->processRule(
                    Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP, $customer);
            }
        }
        $this->_customerNew = false;

        return $this;
    }

    /**
     * Customer newsletter subscription.
     * @param Varien_Object $observer
     * @return void
     */
    public function customerSubscribed($observer)
    {
        $subscribeStatus = $observer->getEvent()->getDataObject()->getSubscriberStatus();
        if ($subscribeStatus == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
            Mage::helper('rewards/behavior')->processRule(
                Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP);
        }
    }

    /**
     * Advanced newsletter subscription.
     * @param Varien_Object $observer
     * @return void
     */
    public function advnCustomerSubscribed($observer)
    {
        Mage::helper('rewards/behavior')->processRule(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP);
    }

    /**
     * Poll vote.
     * @param Varien_Object $observer
     * @return void
     */
    public function afterPollVoteAdd($observer)
    {
        $poll = $observer->getPoll();
        Mage::helper('rewards/behavior')->processRule(Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_VOTE,
            false, false, $poll->getId());
    }

    /**
     * Customer review submit.
     * @param Varien_Object $observer
     * @return Mirasvit_Rewards_Model_Observer_Behavior
     */
    public function reviewSubmit($observer)
    {
        $review = $observer->getEvent()->getObject();
        if ($review->isApproved() && $review->getCustomerId()) {
            Mage::helper('rewards/behavior')->processRule(Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REVIEW,
                $review->getCustomerId(),
                Mage::helper('rewards')->getWebsiteId($review->getStoreId()), $review->getId());
        }

        return $this;
    }

    /**
     * Customer tag submit.
     * @param Varien_Object $observer
     * @return Mirasvit_Rewards_Model_Observer_Behavior
     */
    public function tagSubmit($observer)
    {
        $tag = $observer->getEvent()->getObject();
        if (($tag->getApprovedStatus() == $tag->getStatus()) && $tag->getFirstCustomerId()) {
            Mage::helper('rewards/behavior')->processRule(Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_TAG,
                $tag->getFirstCustomerId(),
                Mage::helper('rewards')->getWebsiteId($tag->getFirstStoreId()), $tag->getId());
        }

        return $this;
    }
}
