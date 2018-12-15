<?php
class EM_Mobapp_Lib_Varien_Data_Form_Element_Design extends Varien_Data_Form_Element_Abstract
{
	public function __construct($attributes=array())
    {
        parent::__construct($attributes);
    }

    public function getElementHtml()
    {
		$childBlock = Mage::app()->getLayout()->createBlock('mobapp/adminhtml_mobapp_edit_tab_design');
		$html = $childBlock->toHtml();

        return $html;
    }
}