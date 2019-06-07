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



class Mirasvit_RewardsSocial_Model_Config
{
    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getFacebookIsActive($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/facebook/is_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getFacebookAppId($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/facebook/app_id', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getFacebookAppVersion($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/facebook/app_version', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getTwitterIsActive($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/twitter/is_active', $store);
    }

    /**
     * @param null|string $store
     *
     * @return string
     */
    public function getTwitterToken($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/twitter/token', $store);
    }

    /**
     * @param null|string $store
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTwitterConsumerKey($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/twitter/consumer_key', $store);
    }

    /**
     * @param null|string $store
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTwitterConsumerSecret($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/twitter/consumer_secret', $store);
    }

    /**
     * @param null|string $store
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTwitterIsTokenActive($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/twitter/token_status', $store);
    }

    /**
     * @param string      $value
     * @param null|string $store
     * @return void
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function setTwitterIsTokenActive($value, $store = null)
    {
        Mage::app()->getStore($store)->setConfig('rewardssocial/twitter/token_status', $value);
    }

    /**
     * @param string      $value
     * @param null|string $store
     * @return void
     */
    public function setTwitterToken($value, $store = null)
    {
        Mage::app()->getStore($store)->setConfig('rewardssocial/twitter/token', $value);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGoogleplusIsActive($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/googleplus/is_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getPinterestIsActive($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/pinterest/is_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getReferIsActive($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/refer/is_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getReferDefaultMessage($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/refer/default_message', $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     * @return mixed
     */
    public function getButtonsBlock($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/display/assign_buttons_to_block', $store);
    }

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isShowOnCategoryBlock($store = null)
    {
        return Mage::getStoreConfig('rewardssocial/display/show_buttons_on_category_page', $store);
    }
    /************************/
}
