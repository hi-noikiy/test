<?php

class EM_Onestepcheckout_Model_Mysql4_Salesdeliveryorder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('onestepcheckout/salesdeliveryorder');
    }
}