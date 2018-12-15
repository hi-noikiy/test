<?php
class EM_Mobapp_Block_Adminhtml_Appstore_Edit_Tabs2 extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('appstore_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('mobapp')->__('App License'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('form_info', array(
			'label'     => Mage::helper('mobapp')->__('App License'),
			'title'     => Mage::helper('mobapp')->__('App License'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit_tab_info')->toHtml(),
		));
	 
	  return parent::_beforeToHtml();
	}
}