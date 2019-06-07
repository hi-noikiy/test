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



class Mirasvit_Fpc_Helper_Cache extends Mage_Core_Helper_Abstract
{
    /**
     * @var bool|array
     */
    protected $_custom;

    /**
     * @var Mirasvit_Fpc_Model_Config
     */
    protected $_config;

    public function __construct()
    {
        $this->_custom = Mage::helper('fpc/custom')->getCustomSettings();
        $this->_config = Mage::getSingleton('fpc/config');
    }

    /**
     * Clean old cache
     * @return void
     */
    public function cleanOldCache()
    {
        $clean = true;
        if ($this->_custom && in_array('getCleanOldCache', $this->_custom)) {
            $clean = Mage::helper('fpc/customDependence')->getCleanOldCach();
        }

        if($clean) {
            $cache = Mage::getSingleton('fpc/cache')->getCacheInstance();
            $frontend = $cache->getFrontend();
            $backend = $frontend->getBackend();
            $backend->clean('old');
            /*@fpc cache clean*/
            if ($this->_config->isDebugFlushCacheLogEnabled()) {
                Mage::log('Flushed expired cache (it is cache which not used). Can be disabled in configuration (Clean old cache).', 
                    null, Mirasvit_Fpc_Model_Config::DEBUG_FLUSH_CACHE_LOG, true);
            }           
        }
    }
}
