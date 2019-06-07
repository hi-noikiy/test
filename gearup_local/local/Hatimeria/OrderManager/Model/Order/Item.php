<?php
/**
 * Period Order
 */

class Hatimeria_OrderManager_Model_Order_Item extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('hordermanager/order_item');
    }

    public function saveIfOrderedAndInStock($items)
    {
        foreach ($items as $itemId => $item) {
            $defaults = array(
                'ordered'   => 0,
                'in_stock'  => 0
            );
            $itemData = array_merge($defaults, $item);
            Mage::getModel('hordermanager/order_item')
                ->load($itemId)
                ->addData($itemData)
                ->save();
        }
    }
} 