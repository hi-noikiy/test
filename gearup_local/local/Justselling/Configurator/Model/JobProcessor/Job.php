<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_tickets
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
/**
 * Job model.
 * 
 * @method getName() 
 * @method getStatus()
 * @method getCreatedAt() 
 */
class Justselling_Configurator_Model_Jobprocessor_Job extends Mage_Core_Model_Abstract {
    
    /** @var Job Status constants */
    const STATUS_UNPROCESSED     = 'UNPROCESSED';
    const STATUS_INITIALISING    = 'INITIALISING';
    const STATUS_FINALIZING      = 'FINALIZING';
    const STATUS_PAUSED          = 'PAUSED';
    const STATUS_RUNNING         = 'RUNNING';
    const STATUS_FINISHED        = 'FINISHED';
    const STATUS_CANCELED        = 'CANCELED';
    
    /** @var The number of minutes the cron for the processor runs */
    const CRON_DELAY_MINUTES     = 2;
    
    /** @var bool Flag for shutdown handler */
    private $_isProcessedNormal = false;
    
    /** @var Internal parameter array, only used on creation with ::createInstance() */
    private $_params = array();
    
	/**
	 * @see Varien_Object::_construct()
	 */
	protected function _construct()	{
	    parent::_construct();
		$this->_init('configurator/jobprocessor_job');
	}

	/** 
	 * Returns the status options.
	 * 
	 * @return open/closed status option hash array  
	 */
	public function getStatusOptionHash() {
	    $options = array(
            self::STATUS_UNPROCESSED => Mage::helper('configurator/job')->__('Unprocessed'),
	        self::STATUS_INITIALISING => Mage::helper('configurator/job')->__('Initializing'),
	        self::STATUS_FINALIZING => Mage::helper('configurator/job')->__('Finalizing'),
	        self::STATUS_PAUSED => Mage::helper('configurator/job')->__('Paused'),
            self::STATUS_RUNNING => Mage::helper('configurator/job')->__('Running'), 
            self::STATUS_FINISHED => Mage::helper('configurator/job')->__('Finished'),
            self::STATUS_CANCELED => Mage::helper('configurator/job')->__('Canceled')
        );
	    return $options;
	}

	public function pause() {
	    
	}
	
	public function continueFromPause() {
	    
	}
	
	public function reset() {
	    
	}
	
	/**
	 * Marks the job as FINISHED.
	 */
	public function finish() {
	    $this->setStatus(self::STATUS_FINISHED);
	    $this->getResource()->finish($this->getId());
	}
	
	/**
	 * Marks the job as normally processed.
	 */
	public function setProcessedNormal() {
	    $this->_isProcessedNormal = true;
	}
	
	/** @return boolean true in case the job has been processed normally. */
	public function isProcessedNormal() {
	    return $this->_isProcessedNormal;
	}
	
	/**
	 * Sets the job status to CANCEL.
	 * 
	 * @param string $by The user canceled the job
	 */
	public function cancel($by='system', $why='') {
	    $this->setStatus(self::STATUS_CANCELED);
	    $this->setCanceledBy($by);
	    $this->setCanceledAt(new Zend_Date());
	    $this->setCanceledWhy($why);
	    $this->getResource()->cancel($this->getId(), $by, $why); // persist  
	}
	
	
	/** @return boolean true in case processing is allowed as at least the max. processes have been reached */
	public function isProcessingAllowed() {
	    $isAllowed = $this->getProcessesActive() < $this->getProcessesMax();
	    return $isAllowed; 
	}
	
	/** @return boolean True in case the processing can continue, depending on the current status */
	public function canProcessingContinue() {
	    $status = $this->getStatus();
	    switch ($status) {
	        case self::STATUS_RUNNING:
	        case self::STATUS_UNPROCESSED:
	        case self::STATUS_FINALIZING:
	            return true;
	        default:
	            return false;
	    }
	}
	
	/**
	 * Updates the job regarding to the given $processed: if true, it will be marked as fully processed, given a numeric
	 * value it will increment the done-counter.
	 * 
	 * @param int|bool $processed
	 */
	public function updateProcessed($processed) {
	    $this->getResource()->updateProcessed($this->getId(), $processed);   
	}

	/**
	 *
	 * @param int $count
	 */
	public function updateProblems($count) {
	    $this->getResource()->updateProblems($this->getId(), $count);
	}
	
	/**
	 * 
	 */
	public function incrementProcessesActive() {
	    $this->getResource()->incrementProcessesActive($this->getId());
	}
	
	/**
	 * Updates the status after a working process has been finished. Decrements the active processes.
	 */
	public function updateStatus() {
	    $this->getResource()->updateStatus($this);    
	}
	
	/**
	 * Sets the FINALIZING status.
	 */
	public function finalize() {
	    $this->setStatus(self::STATUS_FINALIZING);
	    $this->getResource()->finalize($this->getId());
	}
	
	/**
	 * Marks the job as started for the given totals to process. Persists it!
	 * 
	 * @param int $totals
	 */
	public function start($totals) {
	    $this->setProcessesActive(1);
	    $this->setCountTotal($totals);
	    $this->setCountDone(0);
	    $this->setStatus(self::STATUS_RUNNING);
	    $this->save();
	}
	
	/**
	 * Marks the job as initializing. Persists the status.
	 */
	public function initialize() {
	    $this->setProcessesActive(1);
	    $this->setCountDone(0);
	    $this->setStartedAt(new Zend_Date());
	    $this->setStatus(self::STATUS_INITIALISING);
	    $this->save();
	}
	
	/**
	 * Returns the params as key->value array;
	 */
	public function getParameters() {
	    $p = unserialize($this->getData('params'));
	    $pPub = $this->getAsParams('params_public');
	    $params = array_merge($p, $pPub);	    
	    $params = array_merge($this->getParametersDefault(), $params);
	    return $params;
	}
	
	/**
	 * Returns the given field/column as a simple key/value array.
	 * 
	 * @param string $field
	 * @return array 
	 */
	private function getAsParams($field) {
	    $params = array();
	    $p = $this->getData($field);
	    $lines = explode(PHP_EOL, $p);
	    foreach ($lines as $line) {
	        if (strncmp($line, '#', 1) === 0) {
	            continue;
	        }
	        $args = explode('=', trim($line));
	        $params[$args[0]] = $args[1];
	    }
	    return $params;
	}
	
	/**
	 * Returns the default parameters, currently: <br/>
	 * 'runtime_max' <br/>
	 * 
	 * @return array 
	 */
	private function getParametersDefault() {
	    $runtime_max = (self::CRON_DELAY_MINUTES * 60) / 2;
	    return array('runtime_max'=> $runtime_max);
	}
	
	/**
	 * Checks wheter the job is basically valid.
	 * 
	 * @return boolean
	 */
	public function isValid() {
	    try {
	        $model = $this->getResolvedModel();
	        $isValid = $model->isValid($this->getParameters());
	        return $isValid;
	    } catch (Exception $e) {
	        Js_Log::log('Validation check failed for job '.$this->getId().', canceled it, reason:'.$e->getMessage(), $this, Zend_Log::ERR);
	        return false;
	    }
	}
	
	/** @return boolean true in case the Job is finished, false otherwise */
	public function isFinished() {
	    return $this->getStatus() == self::STATUS_FINISHED;
	}
	
	
	/** @return boolean true in case the Job is canceled, false otherwise */
	public function isCanceled() {
	    return $this->getStatus() == self::STATUS_CANCELED;
	}
	
	/** @return boolean true in case the Job is running, false otherwise */
	public function isRunning() {
	    return ($this->getStatus() == self::STATUS_INITIALISING) 
	            || ($this->getStatus() == self::STATUS_RUNNING)
	            || ($this->getStatus() == self::STATUS_FINALIZING);
	}
	
	/** @return boolean true in case the Job is raused, false otherwise */
	public function isPaused() {
	    return ($this->getStatus() == self::STATUS_PAUSED);
	}
	
	/**
	 * Returns the model instance, implementing Justselling_Configurator_Model_Jobprocessor_Processor.
	 * 
	 * @throws Exception
	 * @return Justselling_Configurator_Model_Jobprocessor_Processor
	 */
	public function getResolvedModel() {
	    $model = Mage::getModel(trim($this->getModel()));
	    if (empty($model)) throw new Exception('Unable to load model: '.$this->getModel());
	    if (!($model instanceof Justselling_Configurator_Model_Jobprocessor_Processor)) {
	        throw new Exception('Model does not implement Justselling_Configurator_Model_Jobprocessor_Processor: '.$this->getModel());
	    }
	    return $model;
	}
	
	/**
	 * Creates an empty, unprocessed instance and returns it. The returned instance is not persisted, so a ::save() required.
	 * @return Justselling_Configurator_Model_Jobprocessor_Job
	 */
	public static function createInstance() {
	    /* @var $job Justselling_Configurator_Model_Jobprocessor_Job */
	    $job = Mage::getModel('configurator/jobprocessor_job');
	    $job->setData('status', self::STATUS_UNPROCESSED);
	    $job->setData('name', 'no-name');
	    $job->setData('processes_max', 1);
	    $job->setData('created_at', new Zend_Date());
	    $job->setData('created_by', 'auto');
	    return $job;
	}
	
	/**
	 * Sets the parameters to be used for creating the job.
	 * @param array $data
	 */
	public function setParams(array $data) {
	    $this->setData('params', serialize($data));
	}
	
}