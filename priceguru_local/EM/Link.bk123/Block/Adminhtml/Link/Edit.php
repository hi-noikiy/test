<?php

class EM_Link_Block_Adminhtml_Link_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'link';
        $this->_controller = 'adminhtml_link';
        
        $this->_updateButton('save', 'label', Mage::helper('link')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('link')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('link_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'link_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'link_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('link_data') && Mage::registry('link_data')->getId() ) {
            return Mage::helper('link')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('link_data')->getTitle()));
        } else {
            return Mage::helper('link')->__('Add Item');
        }
    }
}