<?php

class EM_Link_Model_Link extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('link/link');
    }
}