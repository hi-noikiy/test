<?php

/**
 * @copyright   Copyright (c) 2009-2014 Amasty (http://www.amasty.com)
 */
class Amasty_Fpccrawler_Model_Resource_Queue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amfpccrawler/queue');
    }
}
