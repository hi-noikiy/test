<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Block_Adminhtml_Gateway_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'gateway_id';
        $this->_controller = 'adminhtml_gateway';
        $this->_blockGroup = 'helpdesk';

        $this->_updateButton('save', 'label', Mage::helper('helpdesk')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('helpdesk')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('helpdesk')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action + 'back/edit/');
            }
        ";
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function getGateway()
    {
        if (Mage::registry('current_gateway') && Mage::registry('current_gateway')->getId()) {
            return Mage::registry('current_gateway');
        }
    }

    public function getHeaderText()
    {
        if ($gateway = $this->getGateway()) {
            return Mage::helper('helpdesk')->__("Edit Gateway '%s'", $this->htmlEscape($gateway->getName()));
        } else {
            return Mage::helper('helpdesk')->__('Create New Gateway');
        }
    }

    /************************/
}
