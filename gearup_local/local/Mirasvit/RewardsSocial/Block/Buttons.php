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



class Mirasvit_RewardsSocial_Block_Buttons extends Mirasvit_RewardsSocial_Block_Buttons_Abstract
{

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $layout = Mage::app()->getLayout();
        $facebook = $layout->createBlock('rewardssocial/buttons_facebook_like')->setTemplate(
            'mst_rewardssocial/buttons/facebook/like.phtml');
        $twitter = $layout->createBlock('rewardssocial/buttons_twitter_tweet')->setTemplate(
            'mst_rewardssocial/buttons/twitter/tweet.phtml');
        $pinterest = $layout->createBlock('rewardssocial/buttons_pinterest_pin')->setTemplate(
            'mst_rewardssocial/buttons/pinterest/pin.phtml');
        $googleplus = $layout->createBlock('rewardssocial/buttons_googleplus_one')->setTemplate(
            'mst_rewardssocial/buttons/googleplus/one.phtml');
        $referral = $layout->createBlock('rewardssocial/buttons_referral')->setTemplate(
            'mst_rewardssocial/buttons/referral.phtml');
        $this->setChild('facebook.like', $facebook);
        $this->setChild('twitter.tweet', $twitter);
        $this->setChild('pinterest.pin', $pinterest);
        $this->setChild('googleplus.one', $googleplus);
        $this->setChild('referral', $referral);
    }

    /**
     * @return int
     */
    public function getEstimatedEarnPoints()
    {
        $url = $this->getCurrentUrl();
        return Mage::helper('rewards/behavior')->getEstimatedEarnPoints(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_FACEBOOK_LIKE, $this->_getCustomer(), false, $url)
                + Mage::helper('rewards/behavior')->getEstimatedEarnPoints(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_TWITTER_TWEET, $this->_getCustomer(), false, $url)
                + Mage::helper('rewards/behavior')->getEstimatedEarnPoints(
            Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_GOOGLEPLUS_ONE, $this->_getCustomer(), false, $url)

                ;
    }

    /**
     * @return bool
     */
    public function isLikeActive()
    {
        return $this->getConfig()->getFacebookIsActive();
    }

    /**
     * @return bool
     */
    public function isTweetActive()
    {
        return $this->getConfig()->getTwitterIsActive();
    }

    /**
     * @return bool
     */
    public function isPinActive()
    {
        return Mage::registry('current_product') && $this->getConfig()->getPinterestIsActive();
    }

    /**
     * @return bool
     */
    public function isOneActive()
    {
        return $this->getConfig()->getGoogleplusIsActive();
    }

    /**
     * @return bool
     */
    public function isReferralActive()
    {
        return $this->getConfig()->getReferIsActive();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return ($this->isLikeActive() || $this->isTweetActive() || $this->isReferralActive()
                || $this->isPinActive() || $this->isOneActive()
            )
            && (Mage::app()->getRequest()->getControllerName() != 'category' || (
                    Mage::app()->getRequest()->getControllerName() == 'category'
                    && $this->getConfig()->isShowOnCategoryBlock()
                )
            );
    }
}