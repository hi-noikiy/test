<?php

/**
 * Class MageWorkshop_ImportExportReview_Model_Sync
 * @method int|null getLastExportedId
 * @method int|null getLastFailedId
 * @method MageWorkshop_ImportExportReview_Model_Sync setLastFailedId(int|null $value)
 * @method string getApiUsername
 * @method string getApiKey
 * @method string getStoreName
 * @method int|null getFailsCount
 * @method MageWorkshop_ImportExportReview_Model_Sync setFailsCount(int|null $value)
 */
class MageWorkshop_ImportExportReview_Model_Sync extends Mage_Core_Model_Abstract
{
    CONST GET_REVIEWS_LIST         = 'review.list';
    CONST GET_REVIEWS_LIST_BY_SKU  = '';
    CONST BATCH_SIZE               = 6000;
    CONST LOG_FILE                 = 'drie_sync_error.log';
    CONST XML_PATH_LOGS_ENABLE     = 'drie/sync_settings/sync_logs';
    CONST XML_PATH_FAILS_COUNT     = 'drie/sync_settings/fails_limit';
    CONST XML_PATH_EMAIL_RECIPIENT = 'drie/sync_settings/fails_notification_email';
    CONST ERROR_EMAIL_TEMPLATE     = 'drie_fail_sync_email_template';
    CONST XML_PATH_EMAIL_SENDER    = 'drie/sync_settings/sync_store_identity';

    protected function _construct()
    {
        $this->_init('mageworkshop_importexportreview/sync');
    }

    /**
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->getData('store_identity');
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->getData('store_url');
    }

    /**
     * @param int $limit
     * @param bool $singleStoreMode
     * @throws Exception
     */
    public function syncReviews($limit = self::BATCH_SIZE, $singleStoreMode = false)
    {
        /** @var MageWorkshop_ImportExportReview_Model_Resource_Sync_Collection $syncStores */
        $syncStores = $this->getCollection();
        /** @var MageWorkshop_ImportExportReview_Helper_ImportExport $helper */
        $helper = Mage::helper('mageworkshop_importexportreview/importExport');
        if ($singleStoreMode) {
            $syncStores->addFieldToFilter('id', $this->getId());
        }
        $createRatings  = Mage::getStoreConfigFlag('drie/sync_settings/create_ratings');
        $createProsCons = Mage::getStoreConfigFlag('drie/sync_settings/create_proscons');
        $maxWidth       = Mage::getStoreConfig('drie/sync_settings/max_width');
        $maxHeight      = Mage::getStoreConfig('drie/sync_settings/max_height');

        set_time_limit(300);
        /** @var MageWorkshop_ImportExportReview_Model_Sync $syncStore */
        foreach ($syncStores as $syncStore) {
            $url          = $syncStore->getUrl();
            $soapUrl      = rtrim($url, '/') . '/api/soap?wsdl=1';
            $identity     = Mage::getStoreConfig('drie/sync_settings/sync_store_identity');
            $username     = $syncStore->getApiUsername();
            $apiKey       = $syncStore->getApiKey();
            $lastFailedId = $syncStore->getLastFailedId();
            $data         = $singleStoreMode ? array($identity, $limit) : array($identity, $limit, '', $lastFailedId);
            try {
                $reviews = $this->_processRequest($soapUrl, $username, $apiKey, self::GET_REVIEWS_LIST, $data);
                $count   = 0;
                foreach ($reviews as $review) {
                    try {
                        $this->_writeLog('Start processing review with title: ' . $review['title'] . ', for product with sku: ' . $review['sku']);
                        $helper->saveRow($review, true, 0, 0, $createRatings, $createProsCons, $maxWidth, $maxHeight);
                        $this->_writeLog('Saved review with title: ' . $review['title'] . ', for product with sku: ' . $review['sku']);
                        $count++;
                    } catch (MageWorkshop_ImportExportReview_MissingProductException $e) {
                        $this->_writeLog($e->getMessage());
                        continue;
                    } catch (MageWorkshop_ImportExportReview_DuplicateException $e) {
                        $this->_writeLog($e->getMessage());
                        continue;
                    } catch (Exception $e) {
                        if (!$singleStoreMode) {
                            if ($syncStore->getLastFailedId() == $review['entity_id']) {
                                $failsCount = $syncStore->getFailsCount();
                                if ($failsCount == (int)Mage::getStoreConfig(self::XML_PATH_FAILS_COUNT)) {
                                    $this->_sendErrorNotification($review, $syncStore);
                                    continue;
                                } else {
                                    $syncStore->setFailsCount($failsCount + 1);
                                }
                            } else {
                                $syncStore->setLastFailedId($review['entity_id']);
                                $syncStore->setFailsCount(1);
                            }

                            $syncStore->save();
                        }
                        throw $e;
                    }
                }
                if (!$singleStoreMode) {
                    $syncStore->setLastFailedId(null);
                    $syncStore->setFailsCount(null);
                    $syncStore->save();
                }
                $this->_updateHistory($syncStore->getId(), $count);
            } catch (Exception $e) {
                $this->_writeLog($e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * @param $skuList
     * @throws Exception
     */
    public function syncReviewsBySkuList($skuList)
    {
        $url            = $this->getUrl();
        $soapUrl        = rtrim($url, '/') . '/api/soap?wsdl=1';
        $identity       = Mage::getStoreConfig('drie/sync_settings/sync_store_identity');
        $username       = $this->getApiUsername();
        $apiKey         = $this->getApiKey();
        $data           = array($identity, self::BATCH_SIZE, $skuList);
        /** @var MageWorkshop_ImportExportReview_Helper_ImportExport $helper */
        $helper         = Mage::helper('mageworkshop_importexportreview/importExport');
        $createRatings  = Mage::getStoreConfigFlag('drie/sync_settings/create_ratings');
        $createProsCons = Mage::getStoreConfigFlag('drie/sync_settings/create_proscons');
        set_time_limit(300);
        try {
            $reviews = $this->_processRequest($soapUrl, $username, $apiKey, self::GET_REVIEWS_LIST, $data);
            $count = 0;
            foreach ($reviews as $review) {
                try {
                    $helper->saveRow($review, true, 0, 0, $createRatings, $createProsCons);
                    $count++;
                } catch (Exception $e) {
                    $this->_writeLog($e->getMessage());
                    throw $e;
                }
            }
            $this->_updateHistory($this->getId(), $count);
        } catch (Exception $e) {
            $this->_writeLog($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $url
     * @param $username
     * @param $apiKey
     * @param $type
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    protected function _processRequest($url, $username, $apiKey, $type, array $data)
    {
        if (!is_array($data)) {
            throw new Exception(Mage::helper('mageworkshop_importexportreview')->__('Incorrect data for reviews sync request'));
        }
        ini_set('default_socket_timeout', 120);
        ini_set('soap.wsdl_cache_enabled', '0');
        ini_set('soap.wsdl_cache_ttl', '0');
        $soapClient = new SoapClient($url);
        $sessionId = $soapClient->login($username, $apiKey);
        $reviews = $soapClient->call($sessionId, $type, $data);
        if (!is_array($reviews)) {
            if (version_compare(phpversion(), '5.6.0', '>=') && ini_get('always_populate_raw_post_data') >= 0) {
                throw new Exception(Mage::helper('mageworkshop_importexportreview')->__('Your are running PHP 5.6+. The "always_populate_raw_post_data" value should be set to "-1"'));
            }

            throw new Exception('SOAP Error. Please check the logs.');
        }
        return $reviews;
    }

    /**
     * @param $syncId
     * @param int $count
     */
    protected function _updateHistory($syncId, $count)
    {
        /** @var MageWorkshop_ImportExportReview_Model_History $syncHistory */
        $syncHistory = Mage::getModel('mageworkshop_importexportreview/history');
        $syncHistory->setData('last_export', date('Y-m-d H:i:s', time()));
        $syncHistory->setData('sync_id', $syncId);
        $syncHistory->setData('type', MageWorkshop_ImportExportReview_Model_History::TYPE_IMPORT);
        $syncHistory->setData('reviews_count', $count);
        $syncHistory->save();
    }

    /**
     * @param $message
     */
    protected function _writeLog($message)
    {
        if (Mage::getStoreConfigFlag(self::XML_PATH_LOGS_ENABLE)) {
            Mage::log($message, null, self::LOG_FILE);
        }
    }

    /**
     * @param array $reviewData
     * @param MageWorkshop_ImportExportReview_Model_Sync $syncStore
     * @throws Exception
     */
    protected function _sendErrorNotification(Array $reviewData, MageWorkshop_ImportExportReview_Model_Sync $syncStore)
    {
        /* @var $translate Mage_Core_Model_Translate */
        $translate = Mage::getSingleton('core/translate');
        try {
            $translate->setTranslateInline(false);
            $data = array(
                'store_identity' => $syncStore->getIdentity(),
                'review_id'      => $reviewData['entity_id'],
                'review_title'   => $reviewData['title'],
                'sku'            => $reviewData['sku']
            );
            $reviewDataObject = new Varien_Object();
            $reviewDataObject->setData($data);
            $mailTemplate = Mage::getModel('core/email_template');
            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate->setDesignConfig(array('area' => 'frontend'));
            $mailTemplate->sendTransactional(
                self::ERROR_EMAIL_TEMPLATE,
                'general',
                Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                null,
                array('data' => $reviewDataObject)
            );

            if (!$mailTemplate->getSentSuccess()) {
                throw new Exception("Can't send email notification about failed reviews sync");
            }
        } catch (Exception $e) {
            $translate->setTranslateInline(true);
            throw $e;
        }

        $translate->setTranslateInline(true);
    }
}