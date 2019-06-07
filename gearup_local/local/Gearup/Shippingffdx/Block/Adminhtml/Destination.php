<?php

class Gearup_Shippingffdx_Block_Adminhtml_Destination extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = "adminhtml_destination";
        $this->_blockGroup = "gearup_shippingffdx";
        $this->_headerText = Mage::helper("gearup_sds")->__("Destination Manager");
        $this->_addButtonLabel = Mage::helper("gearup_sds")->__("Add destination and number");
        parent::__construct();
    }

}
