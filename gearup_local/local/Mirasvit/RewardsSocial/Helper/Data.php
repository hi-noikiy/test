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


class Mirasvit_RewardsSocial_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCoreStoreOptionArray() {
        $arr = Mage::getModel('core/store')->getCollection()->toArray();
        foreach ($arr['items'] as $value) {
            $result[$value['store_id']] = $value['name'];
        }
        return $result;
    }

    /************************/

    public function getConfig()
    {
        return Mage::getSingleton('rewardssocial/config');
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}