<?php

class Gearup_Activity_Model_Resource_Record extends Mage_Core_Model_Resource_Db_Abstract {

    public function _construct(){
        $this->_init("gearup_activity/record", "entity_id");
    }

}
