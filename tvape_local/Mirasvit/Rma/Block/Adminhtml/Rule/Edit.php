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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Adminhtml_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'rule_id';
        $this->_controller = 'adminhtml_rule';
        $this->_blockGroup = 'rma';

        $this->_updateButton('save', 'label', Mage::helper('rma')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('rma')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('rma')->__('Save And Continue Edit'),
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

    public function getRule()
    {
        if (Mage::registry('current_rule') && Mage::registry('current_rule')->getId()) {
            return Mage::registry('current_rule');
        }
    }

    public function getHeaderText()
    {
        if ($rule = $this->getRule()) {
            return Mage::helper('rma')->__("Edit Rule '%s'", $this->htmlEscape($rule->getName()));
        } else {
            return Mage::helper('rma')->__('Create New Rule');
        }
    }

    /************************/
}
