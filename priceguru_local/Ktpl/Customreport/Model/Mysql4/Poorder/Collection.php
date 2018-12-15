<?php

class Ktpl_Customreport_Model_Mysql4_Poorder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customreport/poorder');
    }
}