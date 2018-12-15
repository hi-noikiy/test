<?php

class EM_AdvertiseLeft_Block_Adminhtml_AdvertiseLeft_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'advertiseleft';
        $this->_controller = 'adminhtml_advertiseleft';
        
        $this->_updateButton('save', 'label', Mage::helper('advertiseleft')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('advertiseleft')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('advertiseleft_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'advertiseleft_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'advertiseleft_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('advertiseleft_data') && Mage::registry('advertiseleft_data')->getId() ) {
            return Mage::helper('advertiseleft')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('advertiseleft_data')->getTitle()));
        } else {
            return Mage::helper('advertiseleft')->__('Add Item');
        }
    }
}