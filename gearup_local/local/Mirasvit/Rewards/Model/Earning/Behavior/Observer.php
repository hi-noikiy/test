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


abstract class Mirasvit_Rewards_Model_Earning_Behavior_Observer
{
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getRewardsCustomer()
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        return Mage::getModel('rewards/customer')->load($customerId);
    }

    public function isCustomerHasPoints($transactionCode)
    {
        return $this->getRewardsCustomer()->getTransaction($transactionCode) ? true : false;
    }
}