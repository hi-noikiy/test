<?php

class FFDX_ShippingBox_Block_Adminhtml_Tracking_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_objectId = 'tracking_id';
    protected $_blockGroup = 'ffdxshippingbox';
    protected $_controller = 'adminhtml_tracking';

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('ffdxshippingbox')->__('Check Tracking');
    }
}