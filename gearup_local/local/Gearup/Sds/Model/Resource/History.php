<?php

class Gearup_Sds_Model_Resource_History extends Mage_Core_Model_Resource_Db_Abstract {

    public function _construct(){
        $this->_init("gearup_sds/history", "history_id");
    }

}
