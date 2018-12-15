<?php

class Ktpl_Customreport_Model_Mysql4_Salespickuporder extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the ordercustomer_id refers to the key field in your database table.
        $this->_init('customreport/salespickuporder', 'pickup_id');
    }
}