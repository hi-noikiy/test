<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_jobprocessor
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
class Justselling_Configurator_Adminhtml_JobController extends Mage_Adminhtml_Controller_Action {
    
   
	/**
	 *
	 */
	public function indexAction() {		
	    Js_Log::log('indexAction', $this);
	    $this->loadLayout()
		->_addContent( $this->getLayout()->createBlock('configurator/adminhtml_jobprocessor_job') )
		->renderLayout();
	}

	/**
	 *
	 */
	public function editAction() {
	    $id = $this->getRequest()->getParam('id');
	    $model = Mage::getModel('configurator/jobprocessor_job')->load((int) $id);
	
	    if( $model->getId() || !$id ) {
	        Mage::register("model_data", $model);
	        $this->loadLayout();
	        $this->_setActiveMenu("configurator/jobprocessor_job");
	        $this->_addBreadcrumb( Mage::helper("configurator/job")->__("Manage Jobs"),  Mage::helper("configurator/job")->__("Edit Jobs"));
	        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
	        $this->_addContent( $this->getLayout()->createBlock("configurator/adminhtml_jobprocessor_job_edit") )
	                ->_addLeft( $this->getLayout()->createBlock("configurator/adminhtml_jobprocessor_job_edit_tabs") );
	        $this->renderLayout();
	    }
	    else {
	        Mage::getSingleton('adminhtml/session')->addError( Mage::helper("configurator/job")->__("Job could not be found.") );
	        $this->_redirect("*/*/");
	    }
	}
	
	/**
	 *
	 */
	public function newAction() {
	    $this->_forward('edit');
	}	
	
	/**
	 * Job Save Action
	 */
	public function saveAction() { 
	    if ($data = $this->getRequest()->getPost()) {
	        
	        $session = Mage::getSingleton('admin/session');
	        $userName = $session->getUser()->getUsername();
            $job = Mage::getModel('configurator/jobprocessor_job');
            $id = $data['id'];
            if ($id) {
	            $job->load($id);
            }
            try {
                if ($data['do_reset']) {
                    /* @var $jobResource Justselling_Jobprocessor_Model_Resource_Mysql4_Job */
                    $jobResource = Mage::getResourceModel('configurator/jobprocessor_job');
                    $jobResource->reset($id);
                } else if ($data['do_pause']) {
                    $jobResource = Mage::getResourceModel('configurator/jobprocessor_job');
                    $jobResource->pause($id);
                } else if ($data['do_cancel']) {
                    $jobResource = Mage::getResourceModel('configurator/jobprocessor_job');
                    $jobResource->cancel($id, $userName, 'Manually in Backend');
                } else if ($data['do_resume']) {
                    $jobResource = Mage::getResourceModel('configurator/jobprocessor_job');
                    $jobResource->resume($id);
                } else {
                    $job->setProcessesMax($data['processes_max']);
                    $job->setModel($data['model']);
                    $job->setName($data['name']);
                    $job->setParamsPublic($data['params_public']);
                    $job->save();
                }                
                $successMsg = Mage::helper('configurator/job')->__('Saved job successfully.');
                Mage::getSingleton('adminhtml/session')->addSuccess($successMsg);
            } catch (Exception $e) {
                $msg = Mage::helper('configurator/job')->__('Problem on saving: '.$e->getMessage());
                Mage::getSingleton('adminhtml/session')->addError($msg);
                Js_Log::log('Problem on saving job via Backend: '.$e->getMessage(), $this, Zend_Log::ALERT);
            }
            Mage::getSingleton('adminhtml/session')->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $job->getId()));
            } else {
                $this->_redirect('*/*/');
            }
	        return;
	    }
	    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('jobprocessor')->__('No data found on save-process.'));
	    $this->_redirect('*/*/');
	}	
	

	
	/**
	 * Deletes a ticket. 
	 * 
	 * Note: Deleting a ticket cascades, so the deletion of a tickets results in deletion of all the related messages. 
	 */
	public function deleteAction() {
	    if ($id = $this->getRequest()->getParam('id')) {
	        try {
	            $job = Mage::getModel('configurator/jobprocessor_job')->load($id);
	            $job->delete();
	            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('configurator/job')->__('Job deleted successfully.'));
	        } catch (Exception $e) {
	            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
	            Js_Log::log("Unexpected problem deleting job {$id}:".$e->getTraceAsString(), Zend_Log::ALERT);
	            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
	        }
	    }
	    $this->_redirect('*/*/');
	}	
	
	

}