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


class Mirasvit_Rewards_Block_Checkout_Abstract extends Mage_Checkout_Block_Cart_Abstract
{
    protected function getPurchase() {
        return Mage::helper('rewards/purchase')->getPurchase();
    }

    public function getEarnPoints()
    {
        if (!$this->getPurchase()) {
            return 0;
        }
        return $this->getPurchase()->getEarnPoints();
    }

    /**
     * @deprecated
     */
    public function getPointsEarned()
    {
        return $this->getEarnPoints();
    }
}