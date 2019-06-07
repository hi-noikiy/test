<?php

class Gearup_Activity_Model_Resource_Expire extends Mage_Core_Model_Resource_Db_Abstract {

    public function _construct(){
        $this->_init("gearup_activity/expire", "entity_id");
    }

}
