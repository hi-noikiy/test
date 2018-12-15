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



class Mirasvit_Helpdesk_Block_Adminhtml_Status_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $status = Mage::registry('current_status');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('General Information')));
        if ($status->getId()) {
            $fieldset->addField('status_id', 'hidden', array(
                'name' => 'status_id',
                'value' => $status->getId(),
            ));
        }
        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Title'),
            'name' => 'name',
            'value' => $status->getName(),
        ));
        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Sort Order'),
            'name' => 'sort_order',
            'value' => $status->getSortOrder(),
        ));

        return parent::_prepareForm();
    }
}
