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



class Mirasvit_Helpdesk_Block_Adminhtml_ThirdPartyEmail_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     * @throws Exception
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save', array(
                    'id'    => $this->getRequest()->getParam('id'),
                    'store' => (int) $this->getRequest()->getParam('store')
                )),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            )
        );

        /** @var Mirasvit_Helpdesk_Model_ThirdPartyEmail $email */
        $email = Mage::registry('current_third_party_email');

        $fieldset = $form->addFieldset(
            'edit_fieldset', array('legend' => Mage::helper('helpdesk')->__('General Information'))
        );
        if ($email->getId()) {
            $fieldset->addField('third_party_email_id', 'hidden', array(
                'name'  => 'third_party_email_id',
                'value' => $email->getId(),
            ));
        }
        $fieldset->addField('store_id', 'hidden', array(
            'name'  => 'store_id',
            'value' => (int) $this->getRequest()->getParam('store'),
        ));

        $fieldset->addField('name', 'text', array(
            'label'              => Mage::helper('helpdesk')->__('Contact Name'),
            'required'           => true,
            'name'               => 'name',
            'value'              => $email->getName(),
        ));
        $fieldset->addField('email', 'text', array(
            'label'    => Mage::helper('helpdesk')->__('Email'),
            'required' => true,
            'name'     => 'email',
            'value'    => $email->getEmail(),
            'class'    => 'validate-email',
        ));
        $fieldset->addField('store_ids', 'multiselect', array(
            'label'    => Mage::helper('helpdesk')->__('Stores'),
            'required' => true,
            'name'     => 'store_ids[]',
            'value'    => $email->getStoreIds(),
            'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));
        $fieldset->addField('department_ids', 'multiselect', array(
            'label'    => Mage::helper('helpdesk')->__('Departments'),
            'required' => true,
            'name'     => 'department_ids[]',
            'value'    => $email->getDepartmentIds(),
            'values'   => Mage::getModel('helpdesk/department')->getCollection()->toOptionArray(true),
        ));
        $fieldset->addField('is_active', 'select', array(
            'label'    => Mage::helper('helpdesk')->__('Is Active'),
            'required' => true,
            'name'     => 'is_active',
            'value'    => $email->getIsActive(),
            'values'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    /************************/
}
