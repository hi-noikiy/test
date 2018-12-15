<?php

class EM_Link_Model_Mysql4_Link extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the link_id refers to the key field in your database table.
        $this->_init('link/link', 'link_id');
    }
}