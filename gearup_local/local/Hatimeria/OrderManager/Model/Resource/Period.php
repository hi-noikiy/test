<?php

class Hatimeria_OrderManager_Model_Resource_Period extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Product to website linkage table
     *
     * @var string
     */

    public function _construct()
    {
        $this->_init('hordermanager/period', 'period_id');
    }
} 