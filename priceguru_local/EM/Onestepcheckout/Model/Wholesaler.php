<?php

class EM_Onestepcheckout_Model_Wholesaler extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('onestepcheckout/wholesaler');
    }
}