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
class Justselling_Configurator_Model_Mysql4_Jobprocessor_Job extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()	{
		$this->_init('configurator/jobprocessor_job', 'id');
	}
	
	
	/**
	 * Updates the job regarding to the given $processed: if true, it will be marked as fully processed, given a numeric
	 * value it will increment the done-counter.
	 * 
	 * @param int $jobId
	 * @param int|bool $processed
	 * @throws Exception
	 */
	public function updateProcessed($jobId, $processed) {
	    if ($processed === true) {
    	    $sql = "UPDATE {$this->getMainTable()} SET count_done = count_total WHERE id = {$jobId}";
	    } elseif (is_numeric($processed)) {
	        $sql = "UPDATE {$this->getMainTable()} SET count_done = count_done+$processed WHERE id = {$jobId}";
	    } else {
	        Js_Log::log("Invalid parameter on updating done-counter: must be boolean or a numeric value! '$processed' given.", $this, Zend_Log::ERR);
	        return;
	    }
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        // We ignore it here! throw new Exception('Unable to update counter on job '.$jobId.' (No affected record)');
	    }
	}
	
	/**
	 * Update the problem counter on the job for the given ID.
	 * 
	 * @param int $jobId
	 * @param int $count
	 * @throws Exception
	 */
	public function updateProblems($jobId, $count) {
	    $sql = "UPDATE {$this->getMainTable()} SET count_problems = count_problems+$count WHERE id = {$jobId}";
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        throw new Exception('Unable to update problem counter on job '.$jobId.' (No affected record)');
	    }
	}
	
	/**
	 *
	 * @param unknown_type $jobId
	 * @throws Exception
	 */
	public function incrementProcessesActive($jobId) {
	    $sql = "UPDATE {$this->getMainTable()} SET processes_active = processes_active+1 WHERE id = {$jobId}";
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        throw new Exception('Unable to increment active processes on job '.$jobId.' (No affected record)');
	    }
	}
	/**
	 * Updates the job status after a processing-step has been done. 
	 * <b>Note: do only call this method after a job process step has been done!</b>
	 *
	 * @param Justselling_Configurator_Model_Jobprocessor_Job $job
	 */
	public function updateStatus(Justselling_Configurator_Model_Jobprocessor_Job $job) {
	    /*
	     * We update the given Job instance with the persisted status (as it already may have been modified by another process). We
	     * achive this by blocking the row in the database.
	     */
	    $now = new Zend_Date();
    	$con = $this->_getWriteAdapter()->beginTransaction();
	    try {
    	    $sql = "SELECT * FROM {$this->getMainTable()} WHERE id = '{$job->getId()}' FOR UPDATE";
    	    $row = $con->fetchRow($sql); // now it is blocked for read!
    	    $job->setStatus($row['status']);
    	    if (!$job->isFinished() && !$job->canProcessingContinue()) { // maybe somebody canceled it in the meantime
    	        $con->rollback();
    	        return;
    	    }
    	    if (!empty($row['finished_at'])) {
        	    $job->setFinishedAt($row['finished_at']);
    	    }
    	    $job->setProcessesActive($row['processes_active']);
    	    /* Is it finished? */
    	    if ($row['count_total'] == $row['count_done'] && !$job->isFinished()) {
    	        $job->setStatus(Justselling_Configurator_Model_Jobprocessor_Job::STATUS_FINALIZING);
    	    }    	    
    	    if ($job->isFinished() && empty($row['finished_at'])) {
    	        $job->setFinishedAt($now);
    	    }
    	    /* Reduce process number */
    	    if ($row['processes_active'] > 0) {
    	        $job->setProcessesActive($row['processes_active'] - 1);
    	    }
    	    /* Update it */
    	    $finishedAt = is_null($job->getFinishedAt()) ? 'NULL' : "'{$now->toString('YYYY-MM-dd HH:mm:ss')}'";
    	    $sql = "UPDATE {$this->getMainTable()} SET status='{$job->getStatus()}', finished_at=$finishedAt, 
    	                processes_active='{$job->getProcessesActive()}' WHERE id='{$job->getId()}'";
    	    $con->exec($sql);
    	    
    	    $con->commit();
	    } catch (Exception $e) {
	        Js_Log::logException($e, $this, 'Unexpected problem on updating job status: '.$e->getMessage());
	        $con->rollback();
	    }
	}
	
	/**
	 * Cancels the job.
	 * The cancel modified the 'processes_active' as no #updateStatus() is called anymore on the job.
	 * 
	 * @param int $id
	 * @param string $by
	 * @throws Exception
	 */
	public function cancel($id, $by, $why) {
	    $why = str_ireplace('\'', '', $why);
	    $status = Justselling_Configurator_Model_Jobprocessor_Job::STATUS_CANCELED;
	    $sql = "UPDATE {$this->getMainTable()} SET canceled_by='$by', canceled_why='$why', status='$status', canceled_at=NOW(), 
	               processes_active = IF (processes_active>0, processes_active=processes_active-1, 0) WHERE id = '$id'";
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        throw new Exception('Job cancel failed (no affected row) for Job '.$id);
	    }
	}
	
	/**
	 * Sets the finalize status to the job with the given ID.
	 * The #updateStatus() method (called later on) takes care about the status related parameters.
	 * @param int $id
	 */
	public function finalize($id) {
	    $status = Justselling_Configurator_Model_Jobprocessor_Job::STATUS_FINALIZING;
	    $sql = "UPDATE {$this->getMainTable()} SET status='$status' WHERE id = '$id'";
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        //throw new Exception('Job cancel failed (no affected row) for Job '.$id);
	    }
	}
	
	/**
	 * Marks the job for the given ID as finished.
	 * 
	 * @param int $id The job ID.
	 * @throws Exception On any internal exception
	 */
	public function finish($id) {
	    $status = Justselling_Configurator_Model_Jobprocessor_Job::STATUS_FINISHED;
	    $sql = "UPDATE {$this->getMainTable()} SET status='$status', finished_at=NOW() WHERE id = '$id'";
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        throw new Exception('Job cancel failed (no affected row) for Job '.$id);
	    }
	}
	
	/**
	 * Marks the job for the given ID as paused.
	 * 
	 * @param int $id The job ID.
	 * @throws Exception On any internal exception
	 */
	public function pause($id) {
	    $status = Justselling_Configurator_Model_Jobprocessor_Job::STATUS_PAUSED;
	    $sql = "UPDATE {$this->getMainTable()} SET status='$status' WHERE id = '$id'";
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        throw new Exception('Job pause failed (no affected row) for Job '.$id);
	    }
	}
	
	/**
	 * Marks the job for the given ID as RUNNING.
	 *
	 * @param int $id The job ID.
	 * @throws Exception On any internal exception
	 */
	public function resume($id) {
	    $status = Justselling_Configurator_Model_Jobprocessor_Job::STATUS_RUNNING;
	    $sql = "UPDATE {$this->getMainTable()} SET status='$status' WHERE id = '$id'";
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        throw new Exception('Job resume failed (no affected row) for Job '.$id);
	    }
	}
	
	/**
	 * Marks the job for the given ID as UNPROCESSED.
	 *
	 * @param int $id The job ID.
	 * @throws Exception On any internal exception
	 */
	public function reset($id) {
	    $status = Justselling_Configurator_Model_Jobprocessor_Job::STATUS_UNPROCESSED;
	    $sql = "UPDATE {$this->getMainTable()} SET status='$status', finished_at=NULL, processes_active=0, count_total=0, count_done=0, count_problems=0, canceled_at=NULL, 
	                canceled_by=NULL, canceled_why=NULL, finished_at=NULL WHERE id = '$id'";
	    $affected = $this->_getWriteAdapter()->exec($sql);
	    if (!$affected) {
	        throw new Exception('Job reset failed (no affected row) for Job '.$id);
	    }
	}	
}