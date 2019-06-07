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



class Mirasvit_Rewards_Model_Config_Source_Notification_Position
{
    public function toArray()
    {
        return array(
            Mirasvit_Rewards_Model_Config::NOTIFICATION_POSITION_ACCOUNT_REWARDS => Mage::helper('rewards')->__('Customer Account > My Reward Points'),
            Mirasvit_Rewards_Model_Config::NOTIFICATION_POSITION_ACCOUNT_REFERRALS => Mage::helper('rewards')->__('Customer Account > My Referrals'),
            Mirasvit_Rewards_Model_Config::NOTIFICATION_POSITION_CART => Mage::helper('rewards')->__('Cart Page'),
            Mirasvit_Rewards_Model_Config::NOTIFICATION_POSITION_CHECKOUT => Mage::helper('rewards')->__('Checkout Page'),
        );
    }
    public function toOptionArray()
    {
        $result = array();
        foreach ($this->toArray() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }

        return $result;
    }

    /************************/
}
