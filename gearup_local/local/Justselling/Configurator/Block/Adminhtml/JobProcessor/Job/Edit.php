<?php

class Justselling_Configurator_Block_Adminhtml_Jobprocessor_Job_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct() {
		parent::__construct();
		$this->_blockGroup = 'configurator';
		$this->_controller = 'adminhtml_jobprocessor_job';
		$this->_mode = 'edit';
		/* @var $job Justselling_Configurator_Model_Jobprocessor_Job */
		$job = Mage::registry('model_data');
		$this->_addButton('restart', array(
		        'label' => Mage::helper('configurator/job')->__('Restart Job'),
		        'onclick' => "saveAndRestart('do_reset')",
		        'class' => 'save',
		), -100);
		
		if (!$job->isFinished()) {
    		$this->_addButton('cancel', array(
    		        'label' => Mage::helper('configurator/job')->__('Cancel Job'),
    		        'onclick' => "saveAndRestart('do_cancel')",
    		        'class' => 'save',
    		), -100);
		}
		if ($job->isRunning()) {
    		$this->_addButton('pause', array(
    		        'label' => Mage::helper('configurator/job')->__('Pause Job'),
    		        'onclick' => "saveAndRestart('do_pause')",
    		        'class' => 'save',
    		), -100);
		}
		if ($job->isPaused()) {
    		$this->_addButton('resume', array(
    		        'label' => Mage::helper('configurator/job')->__('Resume Job'),
		            'onclick' => "saveAndRestart('do_resume')",
    		        'class' => 'save',
    		), -100);
		}
		$this->_formScripts[] = "
		    function saveAndRestart(what){
		        document.forms['edit_form'].elements[what].value = 1;
        		document.forms['edit_form'].submit();
		}
		";
		//$this->_updateButton('save', 'label', Mage::helper('tickets')->__($isNew ? 'Create' : 'Send/Save'));
/*
		if (!$isNew) {
    		$this->_updateButton('delete', 'label', Mage::helper('tickets')->__('Delete ticket'));
    	}        
	*/	
    }
    

    /**
     * @see Mage_Adminhtml_Block_Widget_Container::getHeaderText()
     */
    public function getHeaderText() {
        return Mage::helper('configurator/job')->__('Manage Job');
    }
}