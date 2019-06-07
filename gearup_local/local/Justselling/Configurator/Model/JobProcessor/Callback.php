<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_
 * @copyright   Copyright (c) 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
class Justselling_Configurator_Model_Jobprocessor_Callback {
    
    /** @var Justselling_Configurator_Model_Jobprocessor_Job */
    private $_job;
    
    /**
     * Constructor.
     * 
     * @param Justselling_Jobprocessor_Model_Job $job
     */
    public function __construct(Justselling_Configurator_Model_Jobprocessor_Job $job) {
        if (empty($job)) throw new Exception('job is empty!');
        $this->_job = $job;
    }
    
    /** @return Justselling_Configurator_Model_Jobprocessor_Job  */
    public function getJob() {
        return $this->_job;
    }
    
    /**
     * Updates the number of processed items (adds the number of items) by the given value.
     * 
     * @param int $countProcessed The number of items processed
     */
    public function addProcessed($countProcessed) {
        Js_Log::log("Increased processed counter by $countProcessed on job #{$this->_job->getId()}", $this);
        $this->_job->updateProcessed($countProcessed);
    }

    /**
     * Updates the number of problem items (adds the number of items) by the given value.
     *
     * @param int $countProblems
     */
    public function addProblems($countProblems) {
        Js_Log::log("Increased problem counter by $countProblems on job #{$this->_job->getId()}", $this);
        $this->_job->updateProblems($countProblems);
    }
    
    /**
     * Marks the job as (to be) finalized. Finalize is the previous status before the finish status.
     */
    public function finalizeJob() {
        $this->_job->finalize();
    }
    
    /**
     * Marks the job as finished. A reason could be that no more records to process could be found.
     */
    public function finishJob() {
        $this->_job->updateProcessed(true);
    }
    
    /**
     * Marks the job as canceled.
     */
    public function cancelJob() {
        $this->_job->cancel();
    }
}
?>