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
 * store selection tab
 *
 * @category    Gearup
 * @package     Gearup_EMI
 * @author      Ultimate Module Creator
 */
class Gearup_EMI_Block_Adminhtml_Banks_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Gearup_EMI_Block_Adminhtml_Banks_Edit_Tab_Stores
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('banks');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'banks_stores_form',
            array('legend' => Mage::helper('gearup_emi')->__('Store views'))
        );
        $field = $fieldset->addField(
            'store_id',
            'multiselect',
            array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('gearup_emi')->__('Store Views'),
                'title'    => Mage::helper('gearup_emi')->__('Store Views'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            )
        );
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
        $form->addValues(Mage::registry('current_banks')->getData());
        return parent::_prepareForm();
    }
}
