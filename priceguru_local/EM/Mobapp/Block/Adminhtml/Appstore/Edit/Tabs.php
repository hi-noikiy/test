<?php
class EM_Mobapp_Block_Adminhtml_Appstore_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('appstore_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('mobapp')->__('App Information'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('form_store', array(
			'label'     => Mage::helper('mobapp')->__('App Store'),
			'title'     => Mage::helper('mobapp')->__('App Store'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit_tab_store')->toHtml(),
		));

		$this->addTab('form_slide', array(
			'label'     => Mage::helper('mobapp')->__('Slideshow Iphone/Mobiles'),
			'title'     => Mage::helper('mobapp')->__('Slideshow Iphone/Mobiles'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit_tab_slideshow')->toHtml(),
		));

		$this->addTab('form_slide2', array(
			'label'     => Mage::helper('mobapp')->__('Slideshow Ipad/Tables'),
			'title'     => Mage::helper('mobapp')->__('Slideshow Ipad/Tables'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit_tab_slideshow2')->toHtml(),
		));

		$this->addTab('exfunction', array(
			'label'     => Mage::helper('mobapp')->__('External Functions'),
			'title'     => Mage::helper('mobapp')->__('External Functions'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit_tab_exfunction')->toHtml(),
		));

		/*$this->addTab('form_noti', array(    
			'label'     => Mage::helper('mobapp')->__('Notification'),
			'title'     => Mage::helper('mobapp')->__('Notification'),
			'content'   => $this->getLayout()->createBlock('mobapp/adminhtml_appstore_edit_tab_notification')->toHtml(),
		));*/
	 
	  return parent::_beforeToHtml();
	}
}