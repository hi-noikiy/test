<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * Product Labels - labels management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Main
    extends TM_ProLabels_Block_Adminhtml_Rules_Edit_Tab_Abstract
{
    protected function _prepareForm()
    {
        $model = $this->_getLabelModel();
        $form = $this->getForm();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array(
                'legend'=>Mage::helper('prolabels')->__('General Information'),
                'class' => 'fieldset-wide'
            )
        );

        if ($model->getResourceName() == 'prolabels/system') {
            $fieldset->addField('system_id', 'hidden', array(
                'name' => 'system_id',
            ));
        }

        if ($model->getRulesId()) {
            $fieldset->addField('rules_id', 'hidden', array(
                'name' => 'rules_id',
            ));
        }

        $fieldset->addField('label_name', 'text', array(
            'name'      => 'label_name',
            'label'     => Mage::helper('prolabels')->__('Name'),
            'title'     => Mage::helper('prolabels')->__('Name'),
            'required'  => true
        ));

        $fieldset->addField('store_id', 'multiselect', array(
            'name'      => 'store_id[]',
            'label'     => Mage::helper('prolabels')->__('Store View'),
            'title'     => Mage::helper('prolabels')->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
        ));

        if (Mage::getStoreConfig("prolabels/general/customer_group")) {
            $fieldset->addField('customer_group_ids', 'multiselect', array(
                'name'      => 'customer_group_ids[]',
                'label'     => Mage::helper('prolabels')->__('Customer Groups'),
                'title'     => Mage::helper('prolabels')->__('Customer Groups'),
                'required'  => true,
                'values'    => Mage::getResourceModel('customer/group_collection')->toOptionArray()
            ));
        }

        $fieldset->addField('label_status', 'select', array(
            'label'     => Mage::helper('prolabels')->__('Status'),
            'title'     => Mage::helper('prolabels')->__('Status'),
            'name'      => 'label_status',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('prolabels')->__('Enabled'),
                '0' => Mage::helper('prolabels')->__('Disabled')
            )
        ));
        if (Mage::getStoreConfig("prolabels/general/priority")) {
            $fieldset->addField('use_priority', 'select', array(
                'label'     => Mage::helper('prolabels')->__('Use Label Priority'),
                'title'     => Mage::helper('prolabels')->__('Use Label Priority'),
                'name'      => 'use_priority',
                'options'   => array(
                    '0' => Mage::helper('prolabels')->__('No'),
                    '1' => Mage::helper('prolabels')->__('Yes')
                )
            ));

            $fieldset->addField('priority', 'text', array(
                'name'      => 'priority',
                'label'     => Mage::helper('prolabels')->__('Priority'),
                'title'     => Mage::helper('prolabels')->__('Priority'),
                'required'  => false
            ));
        }

        $form->setValues($model->getData());
        return parent::_prepareForm();
    }
}
