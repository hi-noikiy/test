<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Email_Model_Log extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('amemail/log');
    }
}