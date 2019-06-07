<?php

/**
 * Period Order
 */

class Hatimeria_OrderManager_Model_Period_Order extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('hordermanager/period_order', 'period_has_order_id');
    }

    /**
     * Items
     * @return mixed
     */
    public function getItems()
    {
        if ($this->isObjectNew()) {
            Mage::throwException('Object is not loaded!');
        }

        return Mage::getModel('hordermanager/order_item')->getCollection()
            ->addFieldToFilter('order_id', $this->getOrderId());
    }
} 