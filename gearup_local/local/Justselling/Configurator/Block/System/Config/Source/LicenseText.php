<?php

class Justselling_Configurator_Block_System_Config_Source_LicenseText extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $demo = Mage::helper("configurator")->getLocalConfiguration("demo");
        if ($demo !== 'true') {
            $input = new Varien_Data_Form_Element_Text();
            $input->setForm($element->getForm())
                ->setElement($element)
                ->setValue($element->getValue())
                ->setHtmlId($element->getHtmlId())
                ->setName($element->getName());

            $html  = $input->getHtml();
        } else {
            $html = $this->__('not provided');
        }

        return $html;
    }
}