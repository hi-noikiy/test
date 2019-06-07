<?php

class FFDX_ShippingBox_Block_Adminhtml_Tracking_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('check_form');
        $this->setTitle(Mage::helper('ffdxshippingbox')->__('Tracking'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('tracking_to_check_in_form');
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $form->setHtmlIdPrefix('track_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('ffdxshippingbox')->__('Check Tracking'),
            'class'     => 'fieldset-wide'
        ));

        if ($model->getId()) {
            $fieldset->addField('track_id', 'hidden', array(
                'name'  => 'track_id',
            ));
        }

        $fieldset->addField('tracking_number', 'text', array(
            'name'      => 'tracking_number',
            'label'     => Mage::helper('ffdxshippingbox')->__('Tracking Number'),
            'title'     => Mage::helper('ffdxshippingbox')->__('Tracking Number'),
            'required'  => true,
        ));

        if (!$model->getId()) {
            $model->setData('active', '1');
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}