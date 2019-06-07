<?php

class Justselling_Configurator_Block_Adminhtml_Jobprocessor_Job extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->_blockGroup = 'configurator';
		$this->_controller = 'adminhtml_jobprocessor_job';
		$this->_headerText = Mage::helper('configurator/job')->__('Single Product Jobs');
		parent::__construct();
		$this->removeButton("add");
	}
	
}