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
 * meta information tab
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Block_Adminhtml_Banks_Edit_Tab_Meta extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Gearup_EMI_Block_Adminhtml_Banks_Edit_Tab_Meta
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('banks');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'banks_meta_form',
            array('legend' => Mage::helper('gearup_emi')->__('Meta information'))
        );
        $fieldset->addField(
            'meta_title',
            'text',
            array(
                'label' => Mage::helper('gearup_emi')->__('Meta-title'),
                'name'  => 'meta_title',
            )
        );
        $fieldset->addField(
            'meta_description',
            'textarea',
            array(
                'name'      => 'meta_description',
                'label'     => Mage::helper('gearup_emi')->__('Meta-description'),
              )
        );
        $fieldset->addField(
            'meta_keywords',
            'textarea',
            array(
                'name'      => 'meta_keywords',
                'label'     => Mage::helper('gearup_emi')->__('Meta-keywords'),
            )
        );
        $form->addValues(Mage::registry('current_banks')->getData());
        return parent::_prepareForm();
    }
}
