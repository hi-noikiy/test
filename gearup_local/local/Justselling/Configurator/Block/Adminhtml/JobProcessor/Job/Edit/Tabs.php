<?php

class Justselling_Configurator_Block_Adminhtml_Jobprocessor_Job_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->setId('job_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('configurator/job')->__('Job Information'));
	}

    
	/**
	 * @see Mage_Adminhtml_Block_Widget_Tabs::_beforeToHtml()
	 */
	protected function _beforeToHtml() {
		$this->addTab('form_section', array(
				'label'   => Mage::helper('configurator/job')->__('General Data'),
				'title'   => Mage::helper('configurator/job')->__('General Data'),
				'content' => $this->getLayout()->createBlock('configurator/adminhtml_jobprocessor_job_edit_tab_form')->toHtml(),
		));
		return parent::_beforeToHtml();
	}
}