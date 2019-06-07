<?php

class Hatimeria_OrderManager_Model_Resource_Period_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('hordermanager/period');
    }

    public function toNameHash()
    {
        return $this->_toOptionHash('period_id', 'period');
    }

    public function toOptionArray()
    {
        return $this
            ->addFieldToFilter('is_system', 1)
            ->_toOptionArray('period_id','period');
    }
} 