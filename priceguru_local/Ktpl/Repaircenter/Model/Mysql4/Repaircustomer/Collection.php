<?php

class Ktpl_Repaircenter_Model_Mysql4_Repaircustomer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('repaircenter/repaircustomer');
    }
}