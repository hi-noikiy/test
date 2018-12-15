<?php

class EM_SendSMS_Model_SendSMS extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('sendsms/sendsms');
    }
}