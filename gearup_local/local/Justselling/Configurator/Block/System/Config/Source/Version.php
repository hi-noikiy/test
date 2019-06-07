<?php

class Justselling_Configurator_Block_System_Config_Source_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $version = Mage::helper("configurator")->getConfiguratorVersion();
        $edition = Mage::helper("configurator")->getConfiguratorEdition();
        $html = $version."-".$edition;

        return $html;
    }
}