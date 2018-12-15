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



class Mirasvit_Helpdesk_Block_Adminhtml_ThirdPartyEmail_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $email = Mage::registry('current_third_party_email');

        $fieldset = $form->addFieldset(
            'edit_fieldset',
            array('legend' => Mage::helper('helpdesk')->__('General Information'))
        );
        if ($email->getId()) {
            $fieldset->addField('third_party_email_id', 'hidden', array(
                'name' => 'third_party_email_id',
                'value' => $email->getId(),
            ));
        }
        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Title'),
            'name' => 'name',
            'value' => $email->getName(),
        ));
        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Sort Order'),
            'name' => 'sort_order',
            'value' => $email->getSortOrder(),
        ));

        return parent::_prepareForm();
    }
}
