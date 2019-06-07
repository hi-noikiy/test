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



class Mirasvit_Rewards_Helper_Behavior extends Mage_Core_Helper_Abstract
{
    /**
     * @param string   $ruleType
     * @param bool|int $customerId
     * @param bool|int $websiteId
     * @param bool     $code
     *
     * @return bool
     */
    public function addToQueueRule($ruleType, $customerId = false, $websiteId = false, $code = false)
    {
        if ($code) {
            $code = $ruleType.'-'.$code;
        } else {
            $code = $ruleType;
        }

        $code = urlencode($code);

        if (!$websiteId) {
            $websiteId = Mage::app()->getWebsite()->getId();
        }

        if (!$customer = $this->getCustomer($customerId)) {
            return false;
        }

        if (!$this->checkIsAllowToQueue($customer->getId(), $code)) {
            return false;
        }

        if (!$this->checkIsAllowToProcessRule($customer->getId(), $code)) {
            return false;
        }

        $queue = Mage::getModel('rewards/earning_rule_queue')
            ->setCustomerId($customer->getId())
            ->setWebsiteId((int) $websiteId)
            ->setRuleType($ruleType)
            ->setRuleCode($code)
            ->save()
        ;

        return (bool) $queue->getId();
    }

    /**
     * @param string   $ruleType
     * @param bool|int $customerId
     * @param bool     $websiteId
     * @param bool     $code
     * @param array    $options
     *
     * @return bool
     */
    public function processRule($ruleType, $customerId = false, $websiteId = false, $code = false, $options = array())
    {
        if ($code) {
            $code = $ruleType.'-'.$code;
        } else {
            $code = $ruleType;
        }

        if (!$customer = $this->getCustomer($customerId)) {
            return false;
        }

        if (!$this->checkIsAllowToProcessRule($customer->getId(), $code)) {
            return false;
        }

        $rules = $this->getRules($ruleType, $customer, $websiteId, $code);

        $lastTransaction = false;
        foreach ($rules as $rule) {
            /* @var Mirasvit_Rewards_Model_Earning_Rule $rule */
            $rule->afterLoad();

            $object = new Varien_Object();
            $object->setCustomer($customer);
            if (isset($options['referred_customer'])) {
                $object->setReferredCustomer($options['referred_customer']);
            }
            if (!$rule->validate($object)) {
                continue;
            }

            if (!$this->isInLimit($rule, $customer->getId())) {
                continue;
            }
            $total = $rule->getEarnPoints();
            if (isset($options['order'])) {
                /** @var Mage_Sales_Model_Order $order */
                $order = $options['order'];
                switch ($rule->getEarningStyle()) {
                    case Mirasvit_Rewards_Model_Config::EARNING_STYLE_AMOUNT_SPENT:
                        $subtotal = $order->getGrandTotal();
                        $steps = (int) ($subtotal / $rule->getMonetaryStep());
                        $amount = $steps * $rule->getEarnPoints();
                        if ($rule->getPointsLimit() && $amount > $rule->getPointsLimit()) {
                            $amount = $rule->getPointsLimit();
                        }
                        $total = $amount;
                        break;
                    case Mirasvit_Rewards_Model_Config::EARNING_STYLE_QTY_SPENT:
                        $steps = (int) ($order->getQuote()->getItemsQty() / $rule->getQtyStep());
                        $amount = $steps * $rule->getEarnPoints();
                        if ($rule->getPointsLimit() && $amount > $rule->getPointsLimit()) {
                            $amount = $rule->getPointsLimit();
                        }
                        $total = $amount;
                        break;
                }
            }

            $historyText = $this->parseVariables($rule->getHistoryMessage(), $customer, $options);

            // If rule has customer transfer to other group, assign it
            if ($rule->getTransferToGroup() && ($customer->getGroupId() != $rule->getTransferToGroup())) {
                $customer->setGroupId($rule->getTransferToGroup())
                    ->save();
            }

            $lastTransaction = Mage::helper('rewards/balance')->changePointsBalance($customer, $total,
                $historyText, $code, true, $rule->getEmailMessage());
            if ($lastTransaction) {
                $this->addSuccessMessage($rule->getEarnPoints(), $ruleType);
            }
            if ($rule->getIsStopProcessing()) {
                break;
            }
        }

        return $lastTransaction;
    }

    /**
     * @param string $ruleType
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $websiteId
     * @param bool $code
     * @return bool|int
     */
    public function getEstimatedEarnPoints($ruleType, $customer, $websiteId = false, $code = false)
    {
        if (!$this->checkIsAllowToProcessRule($customer->getId(), $code)) {
            return false;
        }
        if ($code) {
            $code = $ruleType.'-'.$code;
        } else {
            $code = $ruleType;
        }

        $rules = $this->getRules($ruleType, $customer, $websiteId, $code);
        $amount = 0;
        foreach ($rules as $rule) {
            if (!$this->isInLimit($rule, $customer->getId())) {
                continue;
            }
            $amount += $rule->getEarnPoints();
        }

        return $amount;
    }

    /**
     * @param int    $customerId
     * @param string $code
     *
     * @return bool
     */
    protected function checkIsAllowToQueue($customerId, $code)
    {
        $collection = Mage::getModel('rewards/earning_rule_queue')->getCollection()
            ->addFieldToFilter('rule_code', $code)
            ->addFieldToFilter('customer_id', $customerId);

        return $collection->count() == 0;
    }

    /**
     * @param int $customerId
     * @param string $code
     * @return bool
     */
    protected function checkIsAllowToProcessRule($customerId, $code)
    {
        $collection = Mage::getModel('rewards/transaction')->getCollection()
            ->addFieldToFilter('code', $code)
            ->addFieldToFilter('customer_id', $customerId);
        if ($code == Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_LOGIN) {
            $currentDate = Mage::getSingleton('core/date')->gmtDate();
            $collection->getSelect()
                ->where("DATEDIFF('{$currentDate}', main_table.created_at) < 1");
        }
        $isAllow = $collection->count() == 0;

        return $isAllow;
    }

    /**
     * @param string $ruleType
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $websiteId
     * @return Mirasvit_Rewards_Model_Resource_Earning_Rule_Collection
     * @throws Mage_Core_Exception
     */
    protected function getRules($ruleType, $customer, $websiteId = false)
    {
        if (!$websiteId) {
            $websiteId = Mage::app()->getWebsite()->getId();
        }
        $customerGroupId = $customer->getGroupId();
        $rules = Mage::getModel('rewards/earning_rule')->getCollection()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($customerGroupId)
            ->addIsActiveFilter()
            ->addFieldToFilter('type', Mirasvit_Rewards_Model_Earning_Rule::TYPE_BEHAVIOR)
            ->addFieldToFilter('behavior_trigger', $ruleType);

        return $rules;
    }

    /**
     * @param Mirasvit_Rewards_Model_Earning_Rule $rule
     * @param int $customerId
     * @return bool
     */
    protected function isInLimit($rule, $customerId)
    {
        if (!$rule->getPointsLimit()) {
            return true;
        }
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('rewards/transaction');
        $dateBefore = Mage::getModel('core/date')->gmtDate('Y-m-d 00:00:00');
        $sum = (int) $readConnection->fetchOne("SELECT SUM(amount) FROM $table WHERE customer_id=".(int) $customerId.
            " AND code LIKE '{$rule->getBehaviorTrigger()}-%' AND created_at > '$dateBefore'");
        if ($rule->getPointsLimit() > $sum) {
            return true;
        }

        return false;
    }

    /**
     * @param int $customerId
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer($customerId = false)
    {
        if (is_object($customerId)) {
            $customerId = $customerId->getId();
        }
        if (!$customerId) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            if (!$customerId) {
                return false;
            }
        }
        $customer = Mage::getModel('customer/customer')->load($customerId);

        return $customer;
    }

    /**
     * Adds a success message in the frontend (via session).
     *
     * @param int    $points
     * @param string $ruleType
     * @return void
     */
    protected function addSuccessMessage($points, $ruleType)
    {
        $comments = array(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_SIGNUP => $this->__('You received %s for signing up'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_LOGIN => $this->__('You received %s at current login'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_ORDER => $this->__('You received %s for placing an order'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_VOTE => $this->__('You received %s for voting'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_SEND_LINK =>
                $this->__('You received %s for sending this product'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP =>
                $this->__('Thank you for signing up and congratulation! You just earned %s for that!'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_TAG =>
                $this->__('You will receive %s after approving of this tag'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REVIEW =>
                $this->__('You will receive %s after approving of this review'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP =>
                $this->__('You received %s for sign up of referral customer.'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_ORDER =>
                $this->__('You received %s for order of referral customer.'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_BIRTHDAY => $this->__('Happy birthday! You received %s.'),
        );
        $hiddenPoints = Mage::getSingleton('rewards/config')->getDisplayBehaviourNotifications();
        if (isset($comments[$ruleType])) {
            $notification = $this->__($comments[$ruleType], Mage::helper('rewards')->formatPoints($points));
            if (!in_array($ruleType, $hiddenPoints)) {
                Mage::getSingleton('core/session')->addSuccess($notification);
            }
        }
    }

    /**
     * @param string $behaviourTrigger
     * @return void
     */
    public function displayBehaviourNotification($behaviourTrigger)
    {
        $comments = array(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_SIGNUP => $this->__('You will receive %s for signing up'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REVIEW =>
                $this->__('You will receive %s for submitting a review'),
        );

        $points = 0;
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomer()->getGroupId();
        $rules = Mage::getModel('rewards/earning_rule')->getCollection()
            ->addCustomerGroupFilter($customerGroupId)
            ->addFieldToFilter('is_active', true)
            ->addFieldToFilter('type', Mirasvit_Rewards_Model_Config::TYPE_BEHAVIOR)
            ->addFieldToFilter('behavior_trigger', $behaviourTrigger)
        ;

        foreach ($rules as $rule) {
            $points += $rule->getEarnPoints();
        }

        $hiddenPoints = Mage::getSingleton('rewards/config')->getDisplayBehaviourNotifications();
        if (isset($comments[$behaviourTrigger]) && ($points > 0) && !in_array($behaviourTrigger, $hiddenPoints)) {
            $messages = Mage::getSingleton('core/session')->getMessages()
                ->getItemsByType(Mage_Core_Model_Message::SUCCESS);
            $notification = $this->__($comments[$behaviourTrigger], Mage::helper('rewards')->formatPoints($points));
            foreach ($messages as $message) {
                if ($message->getText() == $notification) {
                    return;
                }
            }
            Mage::getSingleton('core/session')->addSuccess($notification);
        }
    }

    /**
     * @param string $text
     * @param Mage_Customer_Model_Customer $customer
     * @param array $options
     * @return string
     */
    public function parseVariables($text, $customer, $options = array())
    {
        $matches = array();
        preg_match_all('/\\{\\{(.*?)\\}\\}/i', $text, $matches);
        $vars = $matches[1];

        foreach ($vars as $variable) {
            if ($variable == 'customer_name' && $customer->getId()) {
                $text = str_replace('{{'. $variable . '}}', $customer->getFirstname() . ' ' . $customer->getLastname(),
                    $text);
            }

            if ($variable == 'referred_customer_name' && isset($options['referred_customer'])) {
                $text = str_replace('{{'. $variable . '}}', $options['referred_customer']->getFirstname() . ' ' .
                    $options['referred_customer']->getLastname(), $text);
            }
        }
        return $text;
    }
}
