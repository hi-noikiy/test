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



class Mirasvit_Fpc_Fpc_FlushCacheController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Mirasvit_Fpc_Model_Cache
     */
    protected $_cache;

    /**
     * @var Mirasvit_Fpc_Model_Config
     */
    protected $_config;

     /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    public function _construct()
    {
         $this->_cache = Mage::getSingleton('fpc/cache');
         $this->_config = Mage::getSingleton('fpc/config');
         $this->_request = Mage::app()->getRequest();
    }

    public function flushAction()
    {
        $debugButtonConfiguration = $this->_config->getDebugButtonConfiguration();
        $fpcTags = $this->_request->getParam('fpcTags');
        $cacheId = $this->_request->getParam('cacheId');

        if(!$debugButtonConfiguration || (!$fpcTags && !$cacheId)) {
            $this->norouteAction();
            return false;
        }

        if ($debugButtonConfiguration == Mirasvit_Fpc_Model_Config::FLUSH_DEPENDING_TAGS_CACHE_BUTTON && $fpcTags) {
                $fpcTags = json_decode($fpcTags, true);
                $fpcTags = $this->prepareTags($fpcTags);
                $this->_cache->clearCacheByTags($fpcTags);
                /*@fpc cache clean*/
                if ($this->_config->isDebugFlushCacheLogEnabled(null, true)) {
                    Mage::log('FPC flush cache with tags in frontend because was used flush cache button: '
                        . implode(" | ",$fpcTags), null, Mirasvit_Fpc_Model_Config::DEBUG_FLUSH_CACHE_LOG, true);
                }
                print_r($fpcTags);
        } elseif ($debugButtonConfiguration == Mirasvit_Fpc_Model_Config::FLUSH_CURRENT_PAGE_CACHE_BUTTON && $cacheId) {
                $cacheId = $cacheId;
                $this->_cache->clearCacheById($cacheId);
                /*@fpc cache clean*/
                if ($this->_config->isDebugFlushCacheLogEnabled(null, true)) {
                    Mage::log('FPC flush cache with cacheId in frontend because was used flush cache button: '
                        . $cacheId, null, Mirasvit_Fpc_Model_Config::DEBUG_FLUSH_CACHE_LOG, true);
                }
                echo $cacheId;
        }

        return true;
    }

    /**
     * @param array $fpcTags
     * @return array
     */
    protected function prepareTags($fpcTags)
    {
        $ignoredTags = array(Mirasvit_Fpc_Model_Config::CACHE_TAG,
            Mirasvit_Fpc_Model_Config::LOGGED_TAG,
            Mirasvit_Fpc_Model_Config::NOTLOGGED_TAG,
        );

        $storeTag =  Mirasvit_Fpc_Model_Config::STORE_TAG;

        foreach ($fpcTags as $tagKey => $tagValue) {
            if (in_array($tagValue, $ignoredTags)
                || stripos($tagValue, $storeTag) !== false) {
                    unset($fpcTags[$tagKey]);
            }
        }

        return $fpcTags;
    }
}
