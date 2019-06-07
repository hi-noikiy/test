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



class Mirasvit_Rewards_Model_Observer_Quote extends Mirasvit_Rewards_Model_Observer_Abstract
{
    /**
     * Events dispatcher, which is fired ONLY after quote delete.
     *
     * @param Mage_Core_Model_Observer $observer
     * @return void
     */
    public function quoteDeleteAfter($observer)
    {
        if (!$quote = $observer->getQuote()) {
            return;
        }
        /** @var Mirasvit_Rewards_Model_Purchase $purchase */
        $purchase = Mage::helper('rewards/purchase')->getByQuote($quote);
        if ($purchase && !$purchase->getOrderId()) {
            $purchase->delete();
        }
    }
}
