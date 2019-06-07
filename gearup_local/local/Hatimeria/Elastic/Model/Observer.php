<?php

class Hatimeria_Elastic_Model_Observer
{
    /**
     * Invokes worker
     */
    public function fillAttributes()
    {
        try {
            Mage::getModel('helastic/worker')->run();
        } catch (Exception $e) {
            Mage::log('Fill attributes for elasticsearch ERROR: ' . $e->getMessage(), Zend_Log::ERR, Hatimeria_Elastic_Model_Worker::LOG_FILE);
        }
    }
} 