<?php

class Gearup_Activity_Model_Record extends Mage_Core_Model_Abstract {

    Const SELL_TYPE = 1;
    Const VIEWED_TYPE = 2;
    Const WISHLIST_TYPE = 3;

    public function _construct(){
        parent::_construct();
        $this->_init("gearup_activity/record");
    }
}
