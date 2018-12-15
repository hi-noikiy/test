<?php

class Onesaas_Connect_Model_Mysql4_Osconnect_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('osconnect/osconnect');
    }
}
