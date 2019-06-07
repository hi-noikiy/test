<?php

class Hatimeria_OrderManager_Model_Resource_Order extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Product to website linkage table
     *
     * @var string
     */

    public function _construct()
    {
        $this->_init('hordermanager/order', 'period_has_order_id');
    }
} 