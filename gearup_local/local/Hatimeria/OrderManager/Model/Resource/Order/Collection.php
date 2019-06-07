<?php

class Hatimeria_OrderManager_Model_Resource_Order_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('hordermanager/order');
    }

    public function toNameHash()
    {
        return $this->_toOptionHash('order_id', 'order');
    }

    public function toOptionArray()
    {
        return $this
            ->addFieldToFilter('is_system', 1)
            ->_toOptionArray('order_id','order');
    }

    public function getCollectionWithCustomIds()
    {
        $collection = $this->getSelect()
            ->joinInner(array('perho' => $this->getTable('hordermanager/period')), 'main_table.period_id=perho.period_id', array(
                    'custom_period_id' => 'custom_period_id',
                    'date_from' => 'date_from',
                    'date_to' => 'date_to'
                )
            )
            ->joinLeft(array('sord' => $this->getTable('sales/order')), 'main_table.order_id=sord.entity_id', array(
                'increment_id' => 'increment_id'
            )
        );

        return $collection;
    }
} 