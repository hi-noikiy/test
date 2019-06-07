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



class Mirasvit_Rewards_Model_Config_Source_Order_Coupons
{
    public function toArray()
    {
        return array(
            Mirasvit_Rewards_Model_Config::COUPONS_ENABLED => Mage::helper('rewards')->__('Enabled'),
            Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_WARNED => Mage::helper('rewards')->__('Disabled with warning'),
            Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_HIDDEN => Mage::helper('rewards')->__('Disabled with block hide'),
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
