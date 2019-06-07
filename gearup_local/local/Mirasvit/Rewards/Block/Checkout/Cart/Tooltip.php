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


class Mirasvit_Rewards_Block_Checkout_Cart_Tooltip extends Mirasvit_Rewards_Block_Checkout_Abstract
{
	public function hasGuestNote()
	{
		if (Mage::getModel('customer/session')->isLoggedIn() && $this->getCustomer()->getId()) {
			return false;
		}
		return true;
	}

	public function _toHtml()
	{
		if ($this->getEarnPoints() == 0) {
			return '';
		}
		return parent::_toHtml();
	}
}