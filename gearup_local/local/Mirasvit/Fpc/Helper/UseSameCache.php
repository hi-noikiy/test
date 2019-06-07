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
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Fpc_Helper_UseSameCache extends Mage_Core_Helper_Abstract
{
    /**
     * Use the same cache for all groups
     *
     * @param int $customerGroupId
     *
     * @return int
     */
    public function getFpcCustomerGroup($customerGroupId)
    {
        $sameCacheForAllGroups = $this->getConfig()->isUseSameCacheForAllGroups();
        if ($customerGroupId != 0
            && $sameCacheForAllGroups == Mirasvit_Fpc_Model_Config::USE_SAME_CACHE_LOGGED_IN) {
            $customerGroupId = 1;
        }

        if ($customerGroupId != 0
            && $sameCacheForAllGroups == Mirasvit_Fpc_Model_Config::USE_SAME_CACHE_ALL) {
            $customerGroupId = 0;
        }

        return $customerGroupId;
    }

    /**
     * @return Mirasvit_Fpc_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('fpc/config');
    }

}