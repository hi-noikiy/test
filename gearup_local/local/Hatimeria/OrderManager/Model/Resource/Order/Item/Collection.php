<?php
/**
 * Collection of Items belongs to Order
 */

class Hatimeria_OrderManager_Model_Resource_Order_Item_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('hordermanager/order_item');
    }
} 