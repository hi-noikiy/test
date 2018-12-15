<?php

class EM_Mobapp_Model_Mysql4_Store extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the mobapp_id refers to the key field in your database table.
        $this->_init('mobapp/store', 'id');
    }
}