<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_System_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'prolabels';
        $this->_controller = 'adminhtml_rules';

        $this->_updateButton('save', 'label', Mage::helper('prolabels')->__('Save Label'));

        $objId = $this->getRequest()->getParam($this->_objectId);

        $this->_updateButton('delete', 'label', Mage::helper('prolabels')->__('Delete Label'));
        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('prolabels')->__('Save And Continue'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        return Mage::helper('prolabels')->__('Add Multi Store Label');
    }

}
