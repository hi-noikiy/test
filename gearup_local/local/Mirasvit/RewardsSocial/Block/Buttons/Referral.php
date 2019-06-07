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



class Mirasvit_RewardsSocial_Block_Buttons_Referral extends Mirasvit_RewardsSocial_Block_Buttons_Abstract
{
    /**
     * @return int
     */
    public function getEstimatedEarnPoints()
    {
        $url = $this->getCurrentUrl();
        return Mage::helper('rewards/behavior')->getEstimatedEarnPoints(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_TWITTER_TWEET, $this->_getCustomer(), false, $url);
    }

    /**
     * @return bool|string
     */
    public function getShareUrl()
    {
        if ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
            if ($product = Mage::registry('current_product')) {
                if ($category = Mage::registry('current_category')) {
                    $categoryId = $category->getId();
                } else {
                    $ids = $product->getCategoryIds();
                    $categoryId = $ids[0];
                }
                return Mage::getBaseUrl() . 'r/' . $customer->getId() . '/' .
                $product->getId() . '/' . $categoryId;
            } elseif ($category = Mage::registry('current_category'))
                return Mage::getBaseUrl() . 'r/' . $customer->getId() . '/' . $category->getId();
            else
            {
                return Mage::getUrl('r/' . $customer->getId());
            }
        }
        return false;
    }
}