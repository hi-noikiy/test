<?php

class Gearup_Activity_Model_Expire extends Mage_Core_Model_Abstract {

    public function _construct(){
        parent::_construct();
        $this->_init("gearup_activity/expire");
    }
}
