<?php

class Gearup_Autoinvoice_Model_Resource_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected $_joinedFields = array();

    protected function _construct() {
        parent::_construct();
        $this->_init("gearup_autoinvoice/history");
    }

}
