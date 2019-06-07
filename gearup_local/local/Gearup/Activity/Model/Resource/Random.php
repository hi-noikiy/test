<?php

class Gearup_Activity_Model_Resource_Random extends Mage_Core_Model_Resource_Db_Abstract {

    public function _construct(){
        $this->_init("gearup_activity/random", "category_random_id");
    }

}
