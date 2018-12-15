<?php
class EM_Mobapp_Block_Adminhtml_Appstore_Edit_Tab_Exfunction extends Mage_Adminhtml_Block_Widget_Form
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

	public function _toHtml(){
		$generalinfo = array();
		if(Mage::registry('mobapp_generalinfo'))
			$generalinfo = Mage::registry('mobapp_generalinfo');

		$this->setTemplate('em_mobapp/manage_exfunc.phtml');
		$link = $generalinfo['external_functions_url'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$link);
		$result=curl_exec($ch);
		curl_close($ch);

		$data = json_decode($result, true);
		$this->assign('data', $data);
		return parent::_toHtml();
	}
}