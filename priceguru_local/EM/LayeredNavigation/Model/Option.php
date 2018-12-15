<?php
class EM_LayeredNavigation_Model_Option extends Mage_Core_Model_Abstract {
    protected function _construct() {
		parent::_construct();
        $this->_init('layerednavigation/option');
    }
} 