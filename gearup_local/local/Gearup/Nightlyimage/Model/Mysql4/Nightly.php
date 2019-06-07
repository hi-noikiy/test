<?php
class Gearup_Nightlyimage_Model_Mysql4_Nightly extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("nightlyimage/nightly", "id");
    }
}