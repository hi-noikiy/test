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


class Mirasvit_RewardsSocial_Helper_Balance extends Mage_Core_Helper_Abstract
{
    public function cancelEarnedPoints($customer, $code)
    {
        if (!$earnedTransaction = $this->getEarnedPointsTransaction($customer, $code)) {
            return false;
        }
        $earnedTransaction->delete();
    }

    public function getEarnedPointsTransaction($customer, $code)
    {
        /** @var Mirasvit_Rewards_Model_Resource_Transaction_Collection $collection */
        $collection = Mage::getModel('rewards/transaction')->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId())
            ;
        $collection->getSelect()->where('code = ?', $code);
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }
}
