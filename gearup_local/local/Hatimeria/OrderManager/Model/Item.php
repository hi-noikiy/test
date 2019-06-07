<?php

class Hatimeria_OrderManager_Model_Item extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('hordermanager/item');
    }

    public function _prepareCollection()
    {

        $collection = Mage::getResourceModel('hordermanager/order_item_collection');
        $collection->addAttributeToSelect('*');

        $collection->load();

        return $collection;
    }
} 