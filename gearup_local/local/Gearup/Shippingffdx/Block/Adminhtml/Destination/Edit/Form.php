<?php

class Gearup_Shippingffdx_Block_Adminhtml_Destination_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            "id"        => "edit_form",
            "action"    => $this->getUrl("*/*/save", array("id" => $this->getRequest()->getParam("id"))),
            "method"    => "post",
            "enctype"   => "multipart/form-data"
        ));
        $form->setFieldNameSuffix('destination');
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset("destination_form", array("legend"=>Mage::helper("gearup_sds")->__("Destination information")));

        $country = Mage::getModel('directory/country')->getResourceCollection()->loadByStore()->toOptionArray(true);
        $countryList = array(array("label" => Mage::helper('gearup_sds')->__('Please Select'), "value" => ""));
        foreach ($country as $attribute) {
            if (!$attribute['value']) {
                continue;
            } else {
                $countryList[] = array("label" => $attribute['label'], "value" => $attribute['value']);
            }
        }

        $fieldset->addField("courier_name", "text", array(
            "label"     => Mage::helper("gearup_sds")->__("Courier Name"),
            "class"     => "required-entry",
            "required"  => true,
            "name"      => "courier_name",
        ));
        $fieldset->addField("courier_nickname", "text", array(
            "label"     => Mage::helper("gearup_sds")->__("Courier Nickname"),
            "class"     => "required-entry",
            "required"  => true,
            "name"      => "courier_nickname",
        ));

        $fieldset->addField('code', 'select', array(
            'name'      => 'code',
            'label'     => Mage::helper('gearup_sds')->__('Countries'),
            'title'     => Mage::helper('gearup_sds')->__('Countries'),
            'required'  => true,
            'values'    => $countryList,
        ));

        $fieldset->addField("number", "text", array(
            "label"     => Mage::helper("gearup_sds")->__("Number"),
            "class"     => "required-entry",
            "required"  => true,
            "name"      => "number"
        ));

        $fieldset->addField("tracking_url", "text", array(
            "label"     => Mage::helper("gearup_sds")->__("Tracking Url"),
            "class"     => "required-entry",
            "required"  => true,
            "name"      => "tracking_url"
        ));


        $formData = array();
        if ( Mage::getSingleton("adminhtml/session")->getDestinationData() ) {
            $formData = Mage::getSingleton("adminhtml/session")->getDestinationData();
            Mage::getSingleton("adminhtml/session")->setDestinationData(null);
        } else if ( Mage::registry("destination_data") ) {
            $formData = Mage::registry("destination_data")->getData();
        }

        $form->setValues($formData);

        return parent::_prepareForm();
    }

}
