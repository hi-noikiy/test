<?php
class Gearup_Sds_Block_Adminhtml_Stockcompare extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller         = 'adminhtml_stockcompare';
        $this->_blockGroup         = 'gearup_sds';
        parent::__construct();
        $this->_headerText         = Mage::helper('gearup_sds')->__('Stock compare');
        $this->_removeButton('add');
        $this->_addButton('back', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => "setLocation('" . Mage::helper("adminhtml")->getUrl('*/sds_sds/index') . "')",
            'class'     => 'back',
        ), '', 9);
    }
}
