<?php

class EM_Onestepcheckout_Model_Mysql4_Salespickuporder extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the ordercustomer_id refers to the key field in your database table.
        $this->_init('onestepcheckout/salespickuporder', 'pickup_id');
    }
}