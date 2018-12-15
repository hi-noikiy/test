<?php
class EM_Mobapp_Block_Adminhtml_Appstore_Edit_Tab_Notification extends Mage_Adminhtml_Block_Widget_Form
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

	public function _toHtml(){
		$this->setTemplate('em_mobapp/manage_noti.phtml');

		return parent::_toHtml();
	}
}