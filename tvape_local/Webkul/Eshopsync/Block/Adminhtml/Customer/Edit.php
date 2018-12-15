<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Block_Adminhtml_Customer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'eshopsync';
        $this->_controller = 'adminhtml_customer';

        $this->_updateButton('save', 'label', Mage::helper('eshopsync')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('eshopsync')->__('Delete Item'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('eshopsync_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'eshopsync_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'eshopsync_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('eshopsync_data') && Mage::registry('eshopsync_data')->getId()) {
            return Mage::helper('eshopsync')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('eshopsync_data')->getTitle()));
        } else {
            return Mage::helper('eshopsync')->__('Add Item');
        }
    }
}
