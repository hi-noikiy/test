<?php

class Admitad_Tracking_Block_Adminhtml_Settings_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_settings';
        $this->_blockGroup = 'tracking';
        $this->_updateButton('save', 'label', Mage::helper('tracking')->__('Save Settings'));
        $this->_addButton(
            'revoke', array(
            'label'     => Mage::helper('tracking')->__('Revoke keys'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/admitad_settings/revoke') . '\')',
            'class'     => 'delete',
            ), -100
        );
        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_removeButton('reset');
    }

    public function getHeaderText()
    {
        return Mage::helper('tracking')->__('Admitad Tracking Settings');
    }
}