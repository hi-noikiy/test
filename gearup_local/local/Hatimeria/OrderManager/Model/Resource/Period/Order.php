<?php
/**
 * Order belongs to Period
 */

class Hatimeria_OrderManager_Model_Resource_Period_Order extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hordermanager/order', 'period_has_order_id');
    }
} 