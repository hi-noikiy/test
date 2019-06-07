<?php

class Gearup_Sds_Model_History extends Mage_Core_Model_Abstract {

    public function _construct(){
        parent::_construct();
        $this->_init("gearup_sds/history");
    }

}
