<?php
class Gearup_Shippingffdx_Block_Adminhtml_Reporthistory extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller         = 'adminhtml_reporthistory';
        $this->_blockGroup         = 'gearup_shippingffdx';
        parent::__construct();
        $this->_headerText         = Mage::helper('adminhtml')->__('Track Changed History');
        $this->_removeButton('add');
        $this->_addButton('back', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => "history.back()",
            'class'     => 'back',
        ), '', 9);
        $this->_addButton('delete', array(
            'label'     => Mage::helper('adminhtml')->__('Delete all history'),
            'onclick'   => "confirmSetLocation('Are you sure?', '" . Mage::helper("adminhtml")->getUrl('*/reportffdxhistory/deleteall') . "')",
            'class'     => 'delete',
        ), '', 10);
    }
}
