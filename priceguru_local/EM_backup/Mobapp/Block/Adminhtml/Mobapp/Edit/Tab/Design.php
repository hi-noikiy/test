<?php
class EM_Mobapp_Block_Adminhtml_Mobapp_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

	public function _toHtml(){
		$this->setTemplate('em_mobapp/app_design.phtml');

		return parent::_toHtml();
	}
}