<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_Core
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_Core_Helper_Data extends Mage_Core_Helper_Abstract
{

    const CORE_MODULE_NAME = 'MageWorkshop_Core';
    const CORE_UNINSTALL_PATH = 'drcore/uninstall';
    const CORE_PACKAGE_FILE = 'Core';

    protected $_customerIdentifier;

    /**
     * @return $this
     */
    public function clearCacheAfterInstall()
    {
        /** @var array $allTypes */
        $allTypes = Mage::app()->useCache();
        foreach($allTypes as $type => $key) {
            Mage::app()->getCacheInstance()->cleanType($type);
            Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function reindexDataAfterInstall()
    {
        $processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
        /** @var Mage_Index_Model_Process $process */
        foreach ($processes as $process) {
            if ($process->getStatus() != Mage_Index_Model_Process::STATUS_RUNNING) {
                $process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_attribute');
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            }
        }
        return $this;
    }

    /**
     * Ping Google ReCaptcha
     * Uses in DetailedReview and CommentOnReview modules
     *
     * @param string $reCaptchaPrivateKey
     * @return bool
     */
    public function pingMageWorkshopReCaptcha($reCaptchaPrivateKey)
    {
        /** @var MageWorkshop_DetailedReview_Model_ReCaptchaWrapper_ReCaptcha $reCaptchaModel */
        $detailedReviewReCaptchaModel = Mage::getModel('detailedreview/reCaptchaWrapper_reCaptcha');

        // Ping DetailedReview ReCaptcha with fake data
        $response = $detailedReviewReCaptchaModel->verifyResponse(
            "",
            $reCaptchaPrivateKey
        );

        // Checking "error-codes" in array. If ping is correct count of errors must be equal 1
        // First element of array must be "missing-input-response"
        return count($response['error-codes']) <= 1;
    }

    /**
     * Ping DetailedReview Facebook
     */
    public function pingDetailedReviewFacebook()
    {
        $config = Mage::helper('detailedreview/config');
        if(!$config->isFBShare()) {
            return false;
        }

        $facebookAppId = $config->getFBShareAppId();

        $curlAdapter = new Mage_HTTP_Client_Curl();
        try {
            $curlAdapter->get(
                sprintf(
                    'https://graph.facebook.com/%s?fields=roles&access_token=%s|%s',
                    $facebookAppId,
                    $facebookAppId,
                    $config->getFBShareAppSecret()
                )
            );
        } catch (Exception $e) {
            Mage::logException($e);
        }
        if (!$curlAdapter->getBody()) {
            return false;
        }

        /** @var Mage_Core_Helper_Data $helperJson */
        $helperJson = Mage::helper('core');
        try {
            $response = $helperJson->jsonDecode($curlAdapter->getBody());
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return $response;
    }
}
