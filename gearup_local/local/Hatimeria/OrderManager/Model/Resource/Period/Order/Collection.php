<?php
/**
 * Collection of Orders belong to Period
 */

class Hatimeria_OrderManager_Model_Resource_Period_Order_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('hordermanager/order');
    }
} 