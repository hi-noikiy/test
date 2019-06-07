<?php
/**
* @copyright Amasty.
*/  
class Amasty_Email_Block_Adminhtml_Template_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId   = 'template_id';
        $this->_blockGroup = 'amemail';
        $this->_controller = 'adminhtml_template';
        
        parent::__construct();
        $this->_removeButton('reset'); 
        $this->_removeButton('save'); 
        $this->_removeButton('back'); 
        
        $this->_addButton('send', array(
            'label'     => Mage::helper('amemail')->__('Send'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ), 1);
        
    }

    public function getHeaderText()
    {
        return Mage::helper('amemail')->__('Send Email');
    }
}