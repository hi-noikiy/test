<?php

class Justselling_Configurator_Block_Adminhtml_Jobprocessor_Job_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
	 */
	protected function _prepareForm() {
	    $customer = null;
	    $session = Mage::getSingleton('admin/session');
	    /* @var $user Mage_Admin_Model_User */
	    $user = $session->getUser();
	    $isJustselling = $user->getUsername() == 'justselling';
	    
	    //Mage::helper('tickets')->log('form model_data:'.print_r($data, true));
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$data = Mage::registry('model_data')->getData();
		$fieldset = $form->addFieldset('job_form', array(
		        'legend' =>Mage::helper('configurator/job')->__('Job Data')
		));
		$fieldset->addField('id', 'hidden', array(
		        'name'      => 'id',
		        'index'     => 'id'
		));
		$fieldset->addField('do_reset', 'hidden', array(
		        'name'      => 'do_reset',
		        'value'     => '0'
		));
		$fieldset->addField('do_cancel', 'hidden', array(
		        'name'      => 'do_cancel',
		        'value'     => '0'
		));
		$fieldset->addField('do_pause', 'hidden', array(
		        'name'      => 'do_pause',
		        'value'     => '0'
		));
		$fieldset->addField('do_resume', 'hidden', array(
		        'name'      => 'do_resume',
		        'value'     => '0'
		));
		$fieldset->addField('name', 'text', array(
		        'label'     => Mage::helper('configurator/job')->__('Name'),
		        'name'      => 'name',
		        'index'     => 'name'
		));
		$jobOptions = Mage::getModel('configurator/jobprocessor_job')->getStatusOptionHash();
		$text = $jobOptions[$data['status']];
		$fieldset->addField('status_info', 'note', array(
		        'label'     => Mage::helper('configurator/job')->__('Status'),
		        'name'      => 'status_info',
		        'text'      => $text
		));
		$fieldset->addField('model', 'text', array(
		        'label'     => Mage::helper('configurator/job')->__('Model'),
		        'name'      => 'model',
		        'index'     => 'model',
		        'readonly'  => ($isJustselling) ? true : false
		));
		$fieldset->addField('processes_max', 'text', array(
		        'label'     => Mage::helper('configurator/job')->__('Maximum Processes'),
		        'name'      => 'processes_max',
		        'index'     => 'processes_max',
		        'readonly'  => ($isJustselling) ? true : false
		));
		$fieldset->addField('params_public', 'textarea', array(
		        'label'     => Mage::helper('configurator/job')->__('Parameters'),
		        'name'      => 'params_public',
		        'index'     => 'params_public'
		));
		$cancelWhy = $data['canceled_why'];
		if (!empty($cancelWhy)) {
		    $fieldset->addField('canceled_why', 'textarea', array(
		            'label'     => Mage::helper('configurator/job')->__('Reason of Cancelation'),
		            'name'      => 'info_canceled_why',
		            'index'     => 'canceled_why',
		            'readonly'  => true
		    ));
		}
		
		if (Mage::getSingleton('adminhtml/session')->getVendorData()) {
		    $form->setValues(Mage::getSingleton('adminhtml/session')->getVendorData());
		    Mage::getSingleton('adminhtml/session')->setVendorData(null);
		} elseif (Mage::registry('model_data')) {
		    $form->setValues(Mage::registry('model_data')->getData());
		}		
		return parent::_prepareForm();
	}
	

	

}