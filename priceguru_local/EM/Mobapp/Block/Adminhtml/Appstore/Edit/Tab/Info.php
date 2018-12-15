<?php
class EM_Mobapp_Block_Adminhtml_Appstore_Edit_Tab_Info extends Mage_Adminhtml_Block_Widget_Form
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

	public function _toHtml(){
		$this->setTemplate('em_mobapp/store_manage_inactive.phtml');

		$data	=	Mage::registry('mobapp_data');
		//echo '<pre>';print_r($data->getData());exit;
		$this->assign('data', $data->getData());
		return parent::_toHtml();
	}
}