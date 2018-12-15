<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Resource_BalanceSend extends Mage_Core_Model_Resource_Db_Abstract
{


    protected function _construct()
    {
        $this->_init('amstcred/customer_balance_send', 'balance_send_id');
    }


}
