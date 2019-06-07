<?php

class Gearup_Sds_Model_Resource_Tracking_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected $_joinedFields = array();

    protected function _construct() {
        parent::_construct();
        $this->_init("gearup_sds/tracking");
    }

}
