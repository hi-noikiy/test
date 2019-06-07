<?php

class Gearup_Shippingffdx_Model_Resource_History extends Mage_Core_Model_Resource_Db_Abstract {

    public function _construct(){
        $this->_init("gearup_shippingffdx/history", "history_id");
    }

}
