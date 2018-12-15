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



class Mirasvit_Helpdesk_Block_Adminhtml_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function getConfig()
    {
        return Mage::getSingleton('helpdesk/config');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            )
        );

        /** @var Mirasvit_Helpdesk_Model_Template $template */
        $template = Mage::registry('current_template');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('General Information')));
        if ($template->getId()) {
            $fieldset->addField('template_id', 'hidden', array(
                'name' => 'template_id',
                'value' => $template->getId(),
            ));
        }
        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('helpdesk')->__('Internal Title'),
            'name' => 'name',
            'value' => $template->getName(),
        ));
        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Is Active'),
            'name' => 'is_active',
            'value' => $template->getIsActive(),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
        if ($this->getConfig()->getGeneralIsWysiwyg()) {
            $fieldset->addField('template', 'editor', array(
                'label' => Mage::helper('helpdesk')->__('Template'),
                'name' => 'template',
                'value' => $template->getTemplate(),
                'config' => Mage::getSingleton('helpdesk/config_wysiwyg')->getConfig(),
                'wysiwyg' => true,
                'style' => 'height:15em',
                'note' => 'You can use variables: [ticket_code], [ticket_name], [ticket_customer_name], [ticket_customer_email], [order_increment_id], [store_name], [user_firstname], [user_lastname], [user_email]',
            ));
        } else {
            $fieldset->addField('template', 'textarea', array(
                'label' => Mage::helper('helpdesk')->__('Template'),
                'name' => 'template',
                'value' => $template->getTemplate(),
                'style' => 'height:25em;width: 500px',
                'note' => 'You can use variables: [ticket_customer_name], [ticket_customer_email], [ticket_code], [store_name], [user_firstname], [user_lastname], [user_email]',
            ));
        }
        $fieldset->addField('store_ids', 'multiselect', array(
            'label' => Mage::helper('helpdesk')->__('Stores'),
            'required' => true,
            'name' => 'store_ids[]',
            'value' => $template->getStoreIds(),
            'values' => Mage::getModel('core/store')->getCollection()->toOptionArray(),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
