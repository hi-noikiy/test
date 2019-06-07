<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_FpcCrawler_Helper_Info extends Mage_Core_Helper_Abstract
{
    /**
     * @var Mirasvit_Fpc_Model_Config
     */
    protected $_config;

    public function __construct()
    {
        $this->_config = Mage::getSingleton('fpc/config');
    }

    public function getCrawlUrlLimitInfo($logged = null)
    {
        $config = Mage::getSingleton('fpccrawler/config');
        $crawlUrlLimit = $logged ? $config->getCrawlUrlLimit(true) : $config->getCrawlUrlLimit();
        if ($crawlUrlLimit >= Mirasvit_FpcCrawler_Model_Config::CRAWL_URL_DEFAULT_LIMIT) {
            $crawlUrlLimit = 'unlimited';
        }
        $html = $this->__('Limit for crawled urls: <b>%s</b>', $crawlUrlLimit);

        return $html;
    }

    public function getCronInfo($logged = null)
    {
        $html = array();

        $crawlerInfo = $logged ? $this->_getLastCronTime('fpc_crawlerlogged') : $this->_getLastCronTime('fpc_crawler');

        $html[] = $this->__('Last cron run time: <b>%s</b>', $this->_getLastCronTime(null));
        $html[] = $this->__('Last crawler job run time: <b>%s</b>', $crawlerInfo);
        $html[] = $this->__('Last URLs import run time: <b>%s</b>', $this->_getLastCronTime('fpc_log_import'));

        $expiredCacheInfo = ($this->_config->isCleanOldCacheEnabled())
            ? $this->_getLastCronTime('fpc_cache_clean_old')
            : 'disabled in FPC configuration';
        $html[] = $this->__('Last cache clear time (expired cache): <b>%s</b>', $expiredCacheInfo);

        return implode('<br>', $html);
    }

    protected function _getLastCronTime($jobCode)
    {
        $time = '-';

        $collection = Mage::getModel('cron/schedule')->getCollection()
            ->setOrder('executed_at', 'desc');
        if ($jobCode) {
            $collection->addFieldToFilter('job_code', $jobCode);
        }

        $collection->getSelect()->limit('1');
        $cron = $collection->getFirstItem();

        if ($cron->getExecutedAt()) {
            $time = Mage::getSingleton('core/date')->date('d.m.Y H:i', strtotime($cron->getExecutedAt()));
        }

        return $time;
    }

    /**
     * Show info if crawler disabled.
     *
     * @param bool $logged
     *
     * @return bool
     */
    public function showExtensionDisabledInfo($logged = null)
    {
        $info = array();
        $storeInfo = array();
        $activeStoreCount = 0;
        $config = Mage::getSingleton('fpccrawler/config');

        foreach (Mage::app()->getStores() as $store) {
            if ($store->getIsActive()) {
                $activeStoreCount += 1;
                if (!$config->isEnabled($logged, $store->getId())) {
                    $storeInfo[] = 'Full Page Cache Crawler disabled for "'.parent::escapeHtml($store->getName()).'" store — '.$store->getBaseUrl().'&nbsp;&nbsp;&nbsp;( ID: '.$store->getId().')
                        in <a href="'.Mage::helper('adminhtml')->getUrl('*/system_config/edit/section/fpccrawler/website/'.$store->getWebsite()->getCode().'/store/'.$store->getCode())
                            .'" target="_blank">System->Configuration->Full Page Cache Crawler</a>';
                }
            }
        }

        if ($activeStoreCount == count($storeInfo) && !$config->isEnabled($logged)) {
            $info[] = 'Full Page Cache Crawler disabled in <a href="'.Mage::helper('adminhtml')->getUrl('*/system_config/edit/section/fpccrawler').'" target="_blank">System->Configuration->Full Page Cache Crawler</a>';
        } else {
            $info = array_merge($info, $storeInfo);
        }

        if ($infoText = implode('<br/>', $info)) {
            Mage::getSingleton('adminhtml/session')->addNotice($infoText);
        }

        return true;
    }

    /**
     * Count of cache files.
     *
     * @param bool $logged
     *
     * @return string
     */
    public function getCacheCountInfo($logged = null)
    {
        $html = '';
        Mage::helper('fpc/cache')->cleanOldCache();
        $cache = Mage::getSingleton('fpc/cache')->getCacheInstance()->getFrontend();
        if (get_class($cache->getBackend()) == 'Zend_Cache_Backend_WinCache') {
            return $html;
        }
        $cacheCount = $logged ? count($cache->getIdsMatchingTags(array('FPC_LOGGED_IN')))
                                : count($cache->getIdsMatchingTags(array('FPC_NOTLOGGED_IN')));

        $html = $this->__('Cache count: <b>%s</b>', $cacheCount);

        return $html;
    }
}
