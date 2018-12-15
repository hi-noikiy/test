<?php
class EM_Mobapp_Block_Adminhtml_Mobapp_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'mobapp';
        $this->_controller = 'adminhtml_mobapp';
        
		$this->removeButton("reset");
		$this->removeButton("save");
        //$this->_updateButton('save', 'label', Mage::helper('mobapp')->__('Register'));
        //$this->_updateButton('delete', 'label', Mage::helper('mobapp')->__('Delete Item'));
		
        /*$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);*/

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('mobapp_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'mobapp_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'mobapp_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('mobapp_data') && Mage::registry('mobapp_data')->getId() ) {
            return Mage::helper('mobapp')->__("Edit App '%s'", $this->htmlEscape(Mage::registry('mobapp_data')->getTitle()));
        } else {
            return Mage::helper('mobapp')->__('Add New App');
        }
    }
}