<?php

class Hatimeria_OrderManager_Model_Resource_Order_Item extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Product to website linkage table
     *
     * @var string
     */

    public function _construct()
    {
        $this->_init('hordermanager/order_item', 'period_order_has_item_id');
    }
}