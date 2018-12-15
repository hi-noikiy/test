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



class Mirasvit_Rma_Block_Adminhtml_Return_Address_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Mirasvit_Rma_Block_Adminhtml_Return_Address_Edit constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'address_id';
        $this->_controller = 'adminhtml_return_address';
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

        return $this;
    }

    /**
     * @return Mirasvit_Rma_Model_Return_Address
     */
    public function getReturnAddress()
    {
        return Mage::registry('current_return_address');
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        $address = $this->getReturnAddress();
        if ($address->getId()) {
            return Mage::helper('rma')->__("Edit Return Address '%s'", $this->htmlEscape($address->getTitle()));
        } else {
            return Mage::helper('rma')->__('Create New Return Address');
        }
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $html = parent::_toHtml();
        $switcher = $this->getLayout()->createBlock('adminhtml/store_switcher');
        $switcher->setUseConfirm(false)->setSwitchUrl(
            $this->getUrl('*/*/*/', array('store' => null, '_current' => true))
        );
        $html = $switcher->toHtml().$html;

        return $html;
    }

    /************************/
}
