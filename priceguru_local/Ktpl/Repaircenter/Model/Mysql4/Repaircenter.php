<?php

class Ktpl_Repaircenter_Model_Mysql4_Repaircenter extends Mage_Core_Model_Mysql4_Abstract
{
     protected $_idFieldName='repair_id';
    public function _construct()
    {    
        // Note that the ordercustomer_id refers to the key field in your database table.
        $this->_init('repaircenter/repaircenter', 'repair_id');
    }
}