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



class Mirasvit_Rewards_Model_System_Config_Source_Behaviour
{
    /**
     * @return array
     */
    public static function toArray()
    {
        $result = array(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_SIGNUP => Mage::helper('rewards')->__('Signing up'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_LOGIN => Mage::helper('rewards')->__('Logging in'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_ORDER => Mage::helper('rewards')->__('Placing an order'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_VOTE => Mage::helper('rewards')->__('Vote'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_SEND_LINK => Mage::helper('rewards')->__('Sending a link'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP =>
                Mage::helper('rewards')->__('Newsletter signup'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_TAG => Mage::helper('rewards')->__('Tagging'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REVIEW => Mage::helper('rewards')->__('Writing a review'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_BIRTHDAY => Mage::helper('rewards')->__('Birthday'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP =>
                Mage::helper('rewards')->__('Referred customer signup'),
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_ORDER =>
                Mage::helper('rewards')->__('Order from referred customer'),
        );
        return $result;
    }

    /**
     * @return array
     */
    public static function toOptionArray()
    {
        $options = self::toArray();
        $result = array();
        foreach ($options as $key => $value) {
            $result[] = array(
                'value' => $key,
                'label' => $value
            );
        }
        return $result;
    }

}