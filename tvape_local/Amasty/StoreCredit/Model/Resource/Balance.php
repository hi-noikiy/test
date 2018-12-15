<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Resource_Balance extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('amstcred/customer_balance', 'balance_id');
    }


    public function loadByCustomerAndWebsite($object, $customerId, $websiteId)
    {
        $read = $this->getReadConnection();
        $data = $read->fetchRow(
            $read->select()
                ->from($this->getMainTable())
                ->where('website_id = ?', $websiteId)
                ->where('customer_id = ?', $customerId)
                ->limit(1)
        );
        if ($data) {
            $object->addData($data);
        }
    }

}
