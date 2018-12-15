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



class Mirasvit_Helpdesk_Block_Adminhtml_Permission_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
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

        /** @var Mirasvit_Helpdesk_Model_Permission $permission */
        $permission = Mage::registry('current_permission');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('General Information')));
        if ($permission->getId()) {
            $fieldset->addField('permission_id', 'hidden', array(
                'name' => 'permission_id',
                'value' => $permission->getId(),
            ));
        }
        $values = Mage::helper('helpdesk')->toAdminRoleOptionArray(Mage::helper('helpdesk')->__('All Roles'));
        $fieldset->addField('role_id', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Role'),
            'name' => 'role_id',
            'value' => $permission->getRoleId(),
            'values' => $values,
        ));

        $values = Mage::getModel('helpdesk/department')->getCollection()->toOptionArray();
        array_unshift($values, array('value' => 0, 'label' => Mage::helper('helpdesk')->__('All Departments')));

        $fieldset->addField('department_ids', 'multiselect', array(
            'label' => Mage::helper('helpdesk')->__('Allows access to tickets of departments'),
            'name' => 'department_ids[]',
            'value' => $permission->getDepartmentIds(),
            'values' => $values,
        ));
        $fieldset->addField('is_ticket_remove_allowed', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Can Delete Tickets'),
            'name' => 'is_ticket_remove_allowed',
            'value' => $permission->getIsTicketRemoveAllowed(),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
        $fieldset->addField('is_message_edit_allowed', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Can Edit Message'),
            'name' => 'is_message_edit_allowed',
            'value' => $permission->getIsMessageEditAllowed(),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
        $fieldset->addField('is_message_remove_allowed', 'select', array(
            'label' => Mage::helper('helpdesk')->__('Can Delete Message'),
            'name' => 'is_message_remove_allowed',
            'value' => $permission->getIsMessageRemoveAllowed(),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
