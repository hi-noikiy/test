<?php
class EM_Mobapp_Block_Adminhtml_Mobapp_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('mobapp_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('mobapp')->__('App Information'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('form_info', array(
			'label'     => Mage::helper('mobapp')->__('App Information'),
			'title'     => Mage::helper('mobapp')->__('App Information'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_mobapp_edit_tab_info')->toHtml(),
		));

		/*$this->addTab('form_design', array(
			'label'     => Mage::helper('mobapp')->__('App Design'),
			'title'     => Mage::helper('mobapp')->__('App Design'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_mobapp_edit_tab_design')->toHtml(),
		));

		$this->addTab('form_module', array(
			'label'     => Mage::helper('mobapp')->__('App Module'),
			'title'     => Mage::helper('mobapp')->__('App Module'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_mobapp_edit_tab_module')->toHtml(),
		));

		$this->addTab('form_content', array(
			'label'     => Mage::helper('mobapp')->__('App Content'),
			'title'     => Mage::helper('mobapp')->__('App Content'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_mobapp_edit_tab_content')->toHtml(),
		));

		/*$this->addTab('form_license', array(
			'label'     => Mage::helper('mobapp')->__('App License'),
			'title'     => Mage::helper('mobapp')->__('App License'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_mobapp_edit_tab_license')->toHtml(),
		));*/

	  return parent::_beforeToHtml();
	}
}