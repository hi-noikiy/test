<?php
class Gearup_Autoinvoice_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller         = 'adminhtml_history';
        $this->_blockGroup         = 'gearup_autoinvoice';
        parent::__construct();
        $this->_headerText         = Mage::helper('adminhtml')->__('Invoice Changed History');
        $this->_removeButton('add');
        $this->_addButton('back', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => "history.back()",
            'class'     => 'back',
        ), '', 9);
        $this->_addButton('delete', array(
            'label'     => Mage::helper('adminhtml')->__('Delete all history'),
            'onclick'   => "confirmSetLocation('Are you sure?', '" . Mage::helper("adminhtml")->getUrl('*/autoinvoice_history/deleteall') . "')",
            'class'     => 'delete',
        ), '', 10);
    }
}
