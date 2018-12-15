<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Resource_BalanceSend_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('amstcred/balanceSend');
    }


    public function addWebsiteFilter($websiteIds)
    {
        $this->getSelect()->where('b.website_id IN (?)', $websiteIds);
        return $this;
    }
}
