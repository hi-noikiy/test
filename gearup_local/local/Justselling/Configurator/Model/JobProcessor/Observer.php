<?php
/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_core_utils
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 * @author      Bodo Schulte
 */
class Justselling_Configurator_Model_Jobprocessor_Observer extends Mage_Core_Model_Abstract {

    /** @var int The maximum number of allowed running processes in parallel */
    const MAX_RUNNING_PROCESSES = 3;

    /**
     *
     */
    public function process() {
        /* Set, as otherwise there is no way to see whether the script finals successful. */
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        
        {
            /*
             * Finalizing jobs.
             */
            $finalizingItems = $this->getJobsFinalizing();
            if ($finalizingItems->count()) {
                /* @var $job Justselling_Configurator_Model_Jobprocessor_Job */
                foreach ($finalizingItems as $job) {
                    register_shutdown_function('Justselling_Configurator_Model_Jobprocessor_Observer::onShutdown', $job);
                    if ($job->isProcessingAllowed()) {
                        try {
                            $model = $job->getResolvedModel();
                            $params = $job->getParameters();
                            $job->incrementProcessesActive();
                            
                            $start = microtime(true);
                            Js_Log::log('Start finalizing job #'.$job->getId().'/'.$job->getName(), $this);
                            $model->finalize($params);
                            $runtime = microtime(true) - $start;
                            Js_Log::log('Finished finalizing job #'.$job->getId().'/'.$job->getName().' runtime:'.$runtime.' sec, mem='.memory_get_peak_usage(true), $this);
                            
                            $job->finish();
                            $job->updateStatus();
                            
                            $this->notify($job);
                            
                        } catch (Exception $e) {
                            Js_Log::logException($e, $this, 'Unexpected problem on finalizing job: '.$job->getId(), $this);
                            $job->cancel('system', $e->getMessage());
                            $this->notify($job, $e);
                        }
                        $job->setProcessedNormal();
                        return;
                    }
                }
            }
        }
        {
            /*
             * Running jobs.
             */
            /* @var $job Justselling_Configurator_Model_Jobprocessor_Job */
            $runningItems = $this->getJobsRunning();
            
            /* Check maximum running processes */
            $processesRunning = 0;
            foreach ($runningItems as $job) { $processesRunning += $job->getProcessesActive(); }
            if ($processesRunning < self::MAX_RUNNING_PROCESSES) {
                if ($runningItems->count()) {
                    foreach ($runningItems as $job) {
                        register_shutdown_function('Justselling_Configurator_Model_Jobprocessor_Observer::onShutdown', $job);
                        if ($job->isProcessingAllowed()) {
                            try {
                                if (!$job || !$job->isValid()) {
                                    throw new Exception('Job validation failed! Please check log file.');
                                }
                                $model = $job->getResolvedModel();
                                $callback = new Justselling_Configurator_Model_Jobprocessor_Callback($job);
                                $params = $job->getParameters();
                                $job->incrementProcessesActive();
                                
                                $start = microtime(true);
                                Js_Log::log('Start processing job #'.$job->getId().'/'.$job->getName(), $this);
                                $model->process($params, $callback);
                                $runtime = microtime(true) - $start;
                                Js_Log::log('Finished processing job #'.$job->getId().'/'.$job->getName().' runtime:'.$runtime.' sec, mem='.memory_get_peak_usage(true), $this);
                                
                                $job->updateStatus();
                                
                            } catch (Exception $e) {
                                Js_Log::logException($e, $this, 'Unexpected problem on continuing running job: '.$job->getId(), $this);
                                $job->cancel('system', $e->getMessage());
                                $this->notify($job, $e);
                            }             
                            $job->setProcessedNormal();
                            return;
                        }
                    }
                }
            } else {
                Js_Log::log('Skipping working on running process as max. parallel allowed processes reached: '.self::MAX_RUNNING_PROCESSES, $this, Zend_Log::INFO);
            }
        }
        {
            /*
             * Unprocessed jobs.
             */
            $unprocessedItems = $this->getJobsUnprocessed();
            if ($unprocessedItems->count()) {
                foreach ($unprocessedItems as $job) {
                    register_shutdown_function('Justselling_Configurator_Model_Jobprocessor_Observer::onShutdown', $job);
                    try {
                        if (!$job || !$job->isValid()) {
                            throw new Exception('Job validation failed! Please check log file.');
                        }
                        $model = $job->getResolvedModel();
                        $params = $job->getParameters();
                        
                        $start = microtime(true);
                        Js_Log::log('Start initializing job #'.$job->getId().'/'.$job->getName(), $this);
                        $job->initialize();
                        $runtime = microtime(true) - $start;
                        Js_Log::log('Finished initializing job #'.$job->getId().'/'.$job->getName().' runtime:'.$runtime.' sec, mem='.memory_get_peak_usage(true), $this);
                        
                        
                        $totals = $model->getTotalToProcess($params);
                        $job->start($totals);
                        $callback = new Justselling_Configurator_Model_Jobprocessor_Callback($job);

                        $start = microtime(true);
                        Js_Log::log('Start first-time processing on job #'.$job->getId().'/'.$job->getName(), $this);
                        $model->process($params, $callback);
                        $runtime = microtime(true) - $start;
                        Js_Log::log('Finished first-time processing job #'.$job->getId().'/'.$job->getName().' runtime:'.$runtime.' sec, mem='.memory_get_peak_usage(true), $this);
                        
                        $job->updateStatus();
                        
                    } catch (Exception $e) {
                        Js_Log::logException($e, $this, 'Unexpected problem on starting unprocessed job: '.$job->getId(), $this);
                        $job->cancel('system', $e->getMessage());
                        $this->notify($job, $e);
                    }
                    $job->setProcessedNormal();
                    return;
                }
            }
        }        
    }
    
    /**
     * Returns all jobs in UNPROCESSED status.
     *
     * @return Varien_Data_Collection_Db
     */
    private function getJobsUnprocessed() {
        /* @var $collection Varien_Data_Collection_Db */
        $collection = Mage::getModel('configurator/jobprocessor_job')->getCollection();
        $collection->addFieldToFilter('status', Justselling_Configurator_Model_Jobprocessor_Job::STATUS_UNPROCESSED);
        $collection->addFieldToFilter('finished_at', array('null' => true));
        $collection->addOrder('created_at', 'asc');
        return $collection;
    }
    
    /**
     * Returns all jobs in RUNNING status.
     * 
     * @return Varien_Data_Collection_Db
     */
    private function getJobsRunning() {
        /* @var $collection Varien_Data_Collection_Db */
        $collection = Mage::getModel('configurator/jobprocessor_job')->getCollection();
        $collection->addFieldToFilter('status', Justselling_Configurator_Model_Jobprocessor_Job::STATUS_RUNNING);
        $collection->addFieldToFilter('finished_at', array('null' => true));
        $collection->addOrder('created_at', 'asc');
        return $collection;
    }
    
    /**
     * Returns all jobs in FINALIZING status.
     *
     * @return Varien_Data_Collection_Db
     */
    private function getJobsFinalizing() {
        /* @var $collection Varien_Data_Collection_Db */
        $collection = Mage::getModel('configurator/jobprocessor_job')->getCollection();
        $collection->addFieldToFilter('status', Justselling_Configurator_Model_Jobprocessor_Job::STATUS_FINALIZING);
        $collection->addFieldToFilter('finished_at', array('null' => true));
        $collection->addOrder('created_at', 'asc');
        return $collection;
    }
    
    
    /**
     *
     * @param Justselling_Jobprocessor_Model_Job $job
     */
    public static function onShutdown($job) {
        $error = error_get_last();
        if ($job && $job->getId() && !$job->isProcessedNormal()) {
            Js_Log::log('Canceled Job on shutdown as unexpected problem occurs: '.print_r($error, true), 'jobprocessor', Zend_Log::ALERT);
            if (!$job->isCanceled()) {
                $job->cancel('system', $error['message']);
            }
        }
    }
    
    
    /**
     * Notifies about the given Job.
     *
     * @param Justselling_Configurator_Model_Jobprocessor_Job $job
     * @param Exception $exception
     */
    private function notify(Justselling_Configurator_Model_Jobprocessor_Job $job, Exception $exception=null) {
        $subject = empty($exception) ? "Job '{$job->getName()}' erfolgreich abgeschlossen" : "Job {$job->getName()} abgebrochen";
        $message = empty($exception) ? "Hallo,\n\nder Job #{$job->getId()} ({$job->getName()}) wurde erfolgreich verarbeitet.
                    \nGesamt: {$job->getCountTotal()}\nVerarbeitet: {$job->getCountDone()}\nFehler: {$job->getCountProblems()}
                    \n\nIhr Justselling-Team\n" : 
                    "Hallo,\n\nder Job #{$job->getId()}/{$job->getName()} wurde aufgrund dieses Fehlers beendet:\n\n{$exception->getMessage()}\n\n{$exception->getTraceAsString()}\n\n
                     Bitte prüfen Sie die Konfiguration und/oder das Logfile.\n\n
                     Ihr Justselling-Team\n";
        $header = 'From: magento@justselling.de' . "\r\n" .
                'Reply-To: admin@justselling.de' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
        $rec = trim(Mage::getStoreConfig('jobprocessor/notifications/emails'));
        if (!empty($rec)) {
            mail($rec, $subject, $message, $header);
            Js_Log::log('Jobprocessor: E-mail sent to: '.$rec, $this, Zend_Log::INFO);
        } else {
            Js_Log::log('Jobprocessor: Skipped sending e-mail notification as no recipient specified.', $this, Zend_Log::INFO);
        }
    }
}
?>
