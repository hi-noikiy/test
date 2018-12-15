<?php

class Dotdigitalgroup_Dotmailer_Block_System_Config_Credentialbutton
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'dotdigitalgroup/dotmailer/system/config/credentialbutton.phtml';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}
