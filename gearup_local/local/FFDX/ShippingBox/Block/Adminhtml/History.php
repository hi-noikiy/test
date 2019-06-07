<?php

class FFDX_ShippingBox_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'ffdxshippingbox';

    public function __construct()
    {
        $this->_controller = 'adminhtml_history';
        $tracking = Mage::registry('current_tracking');
        $this->_headerText = Mage::helper('ffdxshippingbox')->__('FFDX ShippingBox History Of Tracking: ' . $tracking->getTrackingNumber());
        parent::__construct();
        $this->removeButton('add');
    }
}