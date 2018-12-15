<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Customer_Edit_Tab_CustomerBalance_Balance_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $prefix = 'amstcred';
        $form->setHtmlIdPrefix($prefix);
        $form->setFieldNameSuffix('amstcred');

        $customer = Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id'));

        /** @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->addFieldset('amstcred_add_funds',
            array('legend' => $this->__('Update Balance'))
        );


        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_id', 'select', array(
                'name' => 'website_id',
                'label' => $this->__('Website'),
                'title' => $this->__('Website'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(),
            ));

        }
        $fieldset->addField('store_id', 'select',
            array(
                'label' => Mage::helper('amstcred')->__('Send Email from the Following Store View'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
                'name' => 'store_id',
            )
        );

        $text = "<p class='note'>
		Enter positive numbers, e.g. '100' to add credit. Enter negative numbers, e.g. '-100' to deduct balance. Click 'Save Customer' or 'Save and Continue Edit' to update credit.
		</p>";

        $fieldset->addField('amount_delta', 'text', array(
            'name' => 'amount_delta',
            'label' => $this->__('Add or Deduct Credit'),
            'title' => $this->__('Add or Deduct Credit'),
            'after_element_html' => $text
        ));


        $fieldset->addField('comment', 'textarea', array(
            'name' => 'comment',
            'label' => $this->__('Comment'),
            'title' => $this->__('Comment'),
        ));


        if ($customer->isReadonly()) {
            if ($form->getElement('website_id')) {
                $form->getElement('website_id')->setReadonly(true, true);
            }
            $form->getElement('amount_delta')->setReadonly(true, true);
            $form->getElement('comment')->setReadonly(true, true);
        }

        $form->setValues($customer->getData());
        $this->setForm($form);

        return $this;

    }
}
