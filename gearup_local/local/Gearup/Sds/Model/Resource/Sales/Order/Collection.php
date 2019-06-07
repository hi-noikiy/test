<?php
/**
 * Sales Order Collection
 */

class Gearup_Sds_Model_Resource_Sales_Order_Collection extends Hatimeria_OrderManager_Model_Resource_Sales_Order_Collection
{
    public function filterStatus()
    {
        //$this->addFieldToFilter('status', array('neq' => 'canceled'));
        //$this->addFieldToFilter('status', array('neq' => 'closed'));
        $this->addFieldToFilter('status', array('neq' => 'holded'));
        $this->addFieldToFilter('status', array('neq' => 'pending'));
        $this->addFieldToFilter('status', array('neq' => 'pending_payment'));
        $this->addFieldToFilter('status', array('neq' => 'fraud'));
    }
}