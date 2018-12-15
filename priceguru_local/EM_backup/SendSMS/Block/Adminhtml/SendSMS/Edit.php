<?php

class EM_SendSMS_Block_Adminhtml_SendSMS_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'sendsms';
        $this->_controller = 'adminhtml_sendsms';
        
        $this->_updateButton('save', 'label', Mage::helper('sendsms')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('sendsms')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('sendsms_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'sendsms_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'sendsms_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('sendsms_data') && Mage::registry('sendsms_data')->getId() ) {
            return Mage::helper('sendsms')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('sendsms_data')->getTitle()));
        } else {
            return Mage::helper('sendsms')->__('Add Item');
        }
    }
}