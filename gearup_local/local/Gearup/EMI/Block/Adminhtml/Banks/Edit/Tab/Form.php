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
 * Bank edit form tab
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Block_Adminhtml_Banks_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare the form
     *
     * @access protected
     * @return Gearup_EMI_Block_Adminhtml_Banks_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('banks_');
        $form->setFieldNameSuffix('banks');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
                'banks_form', array('legend' => Mage::helper('gearup_emi')->__('Bank'))
        );
        $fieldset->addType(
                'image', Mage::getConfig()->getBlockClassName('gearup_emi/adminhtml_banks_helper_image')
        );
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();



        $fieldset->addField(
                'title', 'text', array(
            'label' => Mage::helper('gearup_emi')->__('Title'),
            'name' => 'title',
            'required' => true,
            'class' => 'required-entry',
                )
        );

        $fieldset->addField(
                'image', 'image', array(
            'label' => Mage::helper('gearup_emi')->__('Image'),
            'name' => 'image',
                )
        );

        $fieldset->addField(
                'processing_fee', 'text', array(
            'label' => Mage::helper('gearup_emi')->__('Processing Fee'),
            'name' => 'processing_fee',
            'required' => true,
            'class' => 'required-entry',
                )
        );
        
        $formValues = Mage::registry('current_banks')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getBanksData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getBanksData());
            Mage::getSingleton('adminhtml/session')->setBanksData(null);
        } elseif (Mage::registry('current_banks')) {
            $formValues = array_merge($formValues, Mage::registry('current_banks')->getData());
        }

        $formValues['options'] = !isset($formValues['options']) ? $formValues['options'] : unserialize($formValues['options']);
        // print_r($formValues['options']);exit;
//        $fieldset->addField(
//                'options', 'textarea', array(
//            'label' => Mage::helper('gearup_emi')->__('Options'),
//            'name' => 'options',
//                )
//        );
        //print_r($formValues['options']);exit;
        $fieldset->addField('options', 'text', array(
            'name' => 'options',
            'label' => 'Installments',
            'class' => 'requried-entry',
            'value' => $formValues['options']
        ));


        $fieldset->addField(
                'terms_condition', 'editor', array(
            'label' => Mage::helper('gearup_emi')->__('Terms & Conditions'),
            'name' => 'terms_condition',
            'config' => $wysiwygConfig,
                )
        );


        $form->getElement('options')->setRenderer(
                $this->getLayout()->createBlock('gearup_emi/adminhtml_banks_edit_tab_manageEMI')
        );



        $fieldset->addField(
                'status', 'select', array(
            'label' => Mage::helper('gearup_emi')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('gearup_emi')->__('Enabled'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('gearup_emi')->__('Disabled'),
                ),
            ),
                )
        );
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                    'store_id', 'hidden', array(
                'name' => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()
                    )
            );
            Mage::registry('current_banks')->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $form->setValues($formValues);
        return parent::_prepareForm();
    }

}
