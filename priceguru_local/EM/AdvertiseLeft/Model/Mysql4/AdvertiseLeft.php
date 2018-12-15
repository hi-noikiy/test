<?php

class EM_AdvertiseLeft_Model_Mysql4_AdvertiseLeft extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the advertiseleft_id refers to the key field in your database table.
        $this->_init('advertiseleft/advertiseleft', 'advertiseleft_id');
    }
}