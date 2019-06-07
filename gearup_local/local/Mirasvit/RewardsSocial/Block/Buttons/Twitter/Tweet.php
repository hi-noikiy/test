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



class Mirasvit_RewardsSocial_Block_Buttons_Twitter_Tweet extends Mirasvit_RewardsSocial_Block_Buttons_Abstract
{
    public function getTweetUrl()
    {
        return Mage::getUrl('rewardssocial/twitter/tweet');
    }

    public function isLiked()
    {
        if (!$customer = $this->_getCustomer()) {
            return false;
        }
        $url = $this->getCurrentUrl();
        if ($earnedTransaction = Mage::helper('rewardssocial/balance')->getEarnedPointsTransaction($customer, Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_TWITTER_TWEET.'-'.$url)) {
            return true;
        }
    }

    public function getEstimatedEarnPoints()
    {
        $url = $this->getCurrentUrl();

        return Mage::helper('rewards/behavior')->getEstimatedEarnPoints(Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_TWITTER_TWEET, $this->_getCustomer(), false, $url);
    }

    /**
     * @deprecated rename
     */
    public function getEstimatedPointsAmount()
    {
        return $this->getEstimatedEarnPoints();
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getEncodedUrl($url)
    {
        return urlencode($url);
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        $url = rtrim(parent::getCurrentUrl(), '/');

        $hash = md5($this->_getCustomer()->getId() + microtime(true));
        if (strpos($url, '?') !== false) {
            $url .= '&'.$hash;
        } else {
            $url .= '?'.$hash;
        }

        return $url;
    }
}
