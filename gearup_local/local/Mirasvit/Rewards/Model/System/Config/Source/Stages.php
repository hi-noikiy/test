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



class Mirasvit_Rewards_Model_System_Config_Source_Stages
{
    /**
     * @return array
     */
    public static function toArray()
    {
        $result = array(
            'override_disable' =>
                Mage::helper('rewards')->__('-- Option is disabled --'),
            Mirasvit_Rewards_Model_Config::STAGE_QUOTE_SAVE_FRONTEND =>
                Mage::helper('rewards')->__('Frontend Quote Save'),
            Mirasvit_Rewards_Model_Config::STAGE_QUOTE_SAVE_BACKEND =>
                Mage::helper('rewards')->__('Backend Quote Save'),
            Mirasvit_Rewards_Model_Config::STAGE_ORDER_PREDISPATCH =>
                Mage::helper('rewards')->__('Order Completion'),
        );
        return $result;
    }
    /**
     * @return array
     */
    public static function toOptionArray()
    {
        $options = self::toArray();
        $result  = array();
        foreach ($options as $key => $value) {
            $result[] = array(
                'value' => $key,
                'label' => $value
            );
        }
        return $result;
    }
}
