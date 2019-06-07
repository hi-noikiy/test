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



class Mirasvit_Rewards_Model_Rewrite_Sendfriend extends Mage_Sendfriend_Model_Sendfriend {

	public function send() {
		parent::send ();
		$product = $this->getProduct();
        Mage::helper('rewards/behavior')->processRule(Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_SEND_LINK,
            false, false, $product->getId());
	}

}