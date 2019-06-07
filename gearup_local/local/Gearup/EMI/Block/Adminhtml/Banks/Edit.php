<?php
/**
 * Gearup_EMI extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Gearup
 * @package        Gearup_EMI
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Bank admin edit form
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Block_Adminhtml_Banks_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'gearup_emi';
        $this->_controller = 'adminhtml_banks';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('gearup_emi')->__('Save Bank')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('gearup_emi')->__('Delete Bank')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('gearup_emi')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_banks') && Mage::registry('current_banks')->getId()) {
            return Mage::helper('gearup_emi')->__(
                "Edit Bank '%s'",
                $this->escapeHtml(Mage::registry('current_banks')->getTitle())
            );
        } else {
            return Mage::helper('gearup_emi')->__('Add Bank');
        }
    }
}
