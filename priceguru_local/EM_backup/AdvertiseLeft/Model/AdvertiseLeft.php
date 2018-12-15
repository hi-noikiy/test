<?php

class EM_AdvertiseLeft_Model_AdvertiseLeft extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('advertiseleft/advertiseleft');
    }
}