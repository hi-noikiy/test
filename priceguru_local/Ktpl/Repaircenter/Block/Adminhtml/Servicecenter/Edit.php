<?php

class Ktpl_Repaircenter_Block_Adminhtml_Servicecenter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'repaircenter';
        $this->_controller = 'adminhtml_servicecenter';
        
        $this->_updateButton('save', 'label', Mage::helper('repaircenter')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('repaircenter')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('servicecenter_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'servicecenter_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'servicecenter_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('servicecenter_data') && Mage::registry('servicecenter_data')->getId() ) {
            return Mage::helper('repaircenter')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('servicecenter_data')->getService_name()));
        } else {
            return Mage::helper('repaircenter')->__('Add Item');
        }
    }
}