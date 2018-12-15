<?php

class Ktpl_Customreport_Block_Adminhtml_Wholesaler_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'customreport';
        $this->_controller = 'adminhtml_wholesaler';
        
        $this->_updateButton('save', 'label', Mage::helper('customreport')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('customreport')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('wholesaler_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'wholesaler_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'wholesaler_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('wholesaler_data') && Mage::registry('wholesaler_data')->getId() ) {
            return Mage::helper('customreport')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('wholesaler_data')->getName()));
        } else {
            return Mage::helper('customreport')->__('Add Item');
        }
    }
}