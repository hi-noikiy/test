<?php

class EM_AdvertiseLeft_Model_Mysql4_AdvertiseLeft_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('advertiseleft/advertiseleft');
    }
}