<?php

class Ktpl_Customreport_Model_Salespickuporder extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('customreport/salespickuporder');
    }   
}