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



class Mirasvit_Rewards_Model_Config_Source_Type
{
    public function toArray()
    {
        return array(
            Mirasvit_Rewards_Model_Earning_Rule::TYPE_PRODUCT => Mage::helper('rewards')->__('Product Rule'),
            Mirasvit_Rewards_Model_Earning_Rule::TYPE_CART => Mage::helper('rewards')->__('Cart Rule'),
            Mirasvit_Rewards_Model_Earning_Rule::TYPE_BEHAVIOR => Mage::helper('rewards')->__('Behavior Rule'),
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
