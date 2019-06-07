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



class Mirasvit_RewardsSocial_Block_Buttons_Abstract extends Mage_Core_Block_Template
{
	/**
	 * @return Mirasvit_Rewards_Model_Config
	 */
	public function getConfig()
    {
        return Mage::getSingleton('rewardssocial/config');
    }

	/**
	 * @return string
	 */
	public function getLocaleCode()
    {
        return Mage::app()->getLocale()->getLocaleCode();
    }

	/**
	 * @return string
	 */
	public function getCurrentUrl()
    {
        if ($product = Mage::registry('current_product')) {
            $url = $product->getProductUrl();
        } elseif ($category = Mage::registry('current_category')) {
            $url = $category->getUrl();
        } else {
            $url = Mage::helper('core/url')->getCurrentUrl();
        }
        // -1 to exclude ? or &
        $pos = strpos($url, "___SID");
        if ($pos !== false) {
            $url = substr($url, 0, $pos - 1);
        }
        $pos = strpos($url, "__SID");
        if ($pos !== false) {
            $url = substr($url, 0, $pos - 1);
        }
        return $url;
    }

	/**
	 * @return string
	 */
	public function getCurrentEncodedUrl()
    {
        $url = $this->getCurrentUrl();
        return urlencode($url);
    }

	/**
	 * @return Mage_Customer_Model_Customer
	 */
	public function _getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

	/**
	 * @return bool
	 */
	public function isAuthorized()
    {
        $customer = $this->_getCustomer();
        if ($customer && $customer->getId() > 0) {
            return true;
        }
    }
}