<?php

class Ktpl_Repaircenter_Model_Mysql4_Servicecenter extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_idFieldName='service_id';
    public function _construct()
    {    
        $this->_init('repaircenter/servicecenter', 'service_id');
    }
}