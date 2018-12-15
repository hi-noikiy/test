<?php
class EM_LayeredNavigation_Model_Resource_Option extends Mage_Core_Model_Resource_Db_Abstract {
    protected function _construct() {
        $this->_init('layerednavigation/option', 'id');
    }
} 