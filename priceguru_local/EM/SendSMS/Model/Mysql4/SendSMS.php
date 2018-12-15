<?php

class EM_SendSMS_Model_Mysql4_SendSMS extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the sendsms_id refers to the key field in your database table.
        $this->_init('sendsms/sendsms', 'sendsms_id');
    }
}