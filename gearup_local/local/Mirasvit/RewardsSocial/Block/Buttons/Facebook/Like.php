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



class Mirasvit_RewardsSocial_Block_Buttons_Facebook_Like extends Mirasvit_RewardsSocial_Block_Buttons_Abstract
{
    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->getConfig()->getFacebookAppId();
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        return $this->getConfig()->getFacebookAppVersion();
    }

    /**
     * @return string
     */
    public function getLikeUrl()
    {
        return Mage::getUrl('rewardssocial/facebook/like');
    }

    /**
     * @return string
     */
    public function getFbShareUrl()
    {
        return Mage::getUrl('rewardssocial/facebook/share');
    }

    /**
     * @return string
     */
    public function getUnlikeUrl()
    {
        return Mage::getUrl('rewardssocial/facebook/unlike');
    }

    /**
     * @return bool
     */
    public function isLiked()
    {
        if (!$customer = $this->_getCustomer()) {
            return false;
        }
        $url = $this->getCurrentUrl();
        if ($earnedTransaction = Mage::helper('rewardssocial/balance')->getEarnedPointsTransaction($customer,
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_FACEBOOK_LIKE.'-'.$url)) {
            return true;
        }
    }

    /**
     * @return int
     */
    public function getEstimatedEarnPoints()
    {
        $url = $this->getCurrentUrl();
        return Mage::helper('rewards/behavior')->getEstimatedEarnPoints(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_FACEBOOK_LIKE, $this->_getCustomer(), false, $url);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getConfig()->getFacebookIsActive();
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }
    }

    /**
     * @return int
     * @deprecated
     */
    public function getEstimatedPointsAmount()
    {
        return $this->getEstimatedEarnPoints();
    }
}