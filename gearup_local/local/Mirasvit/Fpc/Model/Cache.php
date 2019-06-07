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



class Mirasvit_Fpc_Model_Cache
{
    const CUSTOM_FPC_FOLDER = 'cache_fpc';

    protected static $_cache = null;
    public static $cacheDir = null;

     protected static $_productIds = null;

    public static function getCacheInstance()
    {
        if (is_null(self::$_cache)) {
            $options = Mage::app()->getConfig()->getNode('global/fpc');
            if (!$options) {
                self::$_cache = Mage::app()->getCacheInstance();
                $backend = Mage::app()->getCacheInstance()->getFrontend()->getBackend();
                //if filesystem always keep cache in cache_fpc folder
                if ($backend instanceof Zend_Cache_Backend_File) {
                    $customFpcFolder = Mirasvit_Fpc_Model_Cache::CUSTOM_FPC_FOLDER;
                    self::$cacheDir = Mage::getBaseDir('var').DS.$customFpcFolder;
                    Mage::app()->getConfig()->getOptions()->createDirIfNotExists(self::$cacheDir);
                    $options = array('backend_options' => array(
                        'cache_dir' => self::$cacheDir,
                        'hashed_directory_level' => 2,
                    ));
                    self::$_cache = Mage::getModel('core/cache', $options);
                }
                return self::$_cache;
            }

            $options = $options->asArray();

            foreach (array('backend_options', 'slow_backend_options') as $tag) {
                if (!empty($options[$tag]['cache_dir'])) {
                    self::$cacheDir = Mage::getBaseDir('var').DS.$options[$tag]['cache_dir'];
                    $options[$tag]['cache_dir'] = self::$cacheDir;
                    Mage::app()->getConfig()->getOptions()->createDirIfNotExists($options[$tag]['cache_dir']);
                }
            }

            self::$_cache = Mage::getModel('core/cache', $options);
        }

        return self::$_cache;
    }

    public function getConfig()
    {
        return Mage::getSingleton('fpc/config');
    }

    public function cleanByLimits()
    {
        if ($this->getConfig()->isCleanOldCacheEnabled()) {
            Mage::helper('fpc/cache')->cleanOldCache();
        }
        if (Mage::helper('fpc')->getCacheSize() > Mage::getSingleton('fpc/config')->getMaxCacheSize()
            || Mage::helper('fpc')->getCacheNumber() > Mage::getSingleton('fpc/config')->getMaxCacheNumber()) {
                Mirasvit_Fpc_Model_Cache::getCacheInstance()->clean(Mirasvit_Fpc_Model_Processor::CACHE_TAG);
                /*@fpc cache clean*/
                if (Mage::getSingleton('fpc/config')->isDebugFlushCacheLogEnabled()) {
                    Mage::log('FPC flush cache by limits Max. Cache Size or Max. Number of Cache Files.', null, Mirasvit_Fpc_Model_Config::DEBUG_FLUSH_CACHE_LOG, true);
                }
        }

        return $this;
    }

    /* not used */
    public function clearAll()
    {
        try {
            $allTypes = Mage::app()->useCache();
            foreach ($allTypes as $type => $blah) {
                Mage::app()->getCacheInstance()->cleanType($type);
                /*@fpc cache clean*/
                if (Mage::getSingleton('fpc/config')->isDebugFlushCacheLogEnabled()) {
                    Mage::log('FPC flush all cache ( function clearAll ).', null, Mirasvit_Fpc_Model_Config::DEBUG_FLUSH_CACHE_LOG, true);
                }
            }
        } catch (Exception $e) {
        }
    }

    public function onCleanCache($observer)
    {
        $cmsTagKey = false;
        try { //if we can't get Cache Tags Level
            $tags = $observer->getEvent()->getData('tags');
            if ($tags && is_array($tags)) { //check cms page tags for flushing
                $cmsTagKey = array_search('cms_page', $tags);
            }
            $cacheTagslevelLevel = $this->getConfig()->getCacheTagslevelLevel();
            if (($cmsTagKey !== false
                && $cacheTagslevelLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_EMPTY)
                || ($cacheTagslevelLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL
                    && $cacheTagslevelLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_EMPTY
                    && $cacheTagslevelLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL_PREFIX)
            ) {
                    $fpcTags = $observer->getTags();
                    $fpcTags = $this->addCategoryTags($fpcTags);
                    self::getCacheInstance()->clean($fpcTags);
                    /*@fpc cache clean*/
                    if (Mage::getSingleton('fpc/config')->isDebugFlushCacheLogEnabled()) {
                        Mage::log('FPC flush cache using event application_clean_cache (Mage/Core/Model/App.php).', null, Mirasvit_Fpc_Model_Config::DEBUG_FLUSH_CACHE_LOG, true);
                    }
            }
        } catch (Exception $e) { }

        return $this;
    }

    /**
     * Add category tags after reindex (AsyncIndex compatibility)
     *
     * @param array $fpcTags
     * @return array
     */
    private function addCategoryTags($fpcTags) 
    {
        if (!Mage::helper('core')->isModuleEnabled('Mirasvit_AsyncIndex')) {
            return $fpcTags;
        }
        $productIds = [];
        $cats = [];
        foreach ((array)$fpcTags as $tag)             // typecast to array when $fpcTags is single string
        {         
            if (strpos($tag, Mirasvit_Fpc_Model_Config::CATALOG_PRODUCT_TAG) !== false) {
                if (isset(self::$_productIds[$tag])) {
                    $productIds[] = str_replace(Mirasvit_Fpc_Model_Config::CATALOG_PRODUCT_TAG, '', $tag);
                } else {
                    self::$_productIds[$tag] = true;
                }
            }
        }
        if ($productIds) {
            foreach ($productIds as $productId) {
                $obj = new Varien_Object();
                $cat = Mage::getResourceSingleton('catalog/product')->getCategoryIds($obj->setId($productId));
                $cats = array_merge($cats, $cat);
            }
        }

        if ($cats) {
            $cats = array_unique($cats);
            foreach ($cats as $cat) {
                $fpcTags[] = Mirasvit_Fpc_Model_Config::CATALOG_CATEGORY_TAG . $cat;
            }
            $fpcTags = array_unique($fpcTags); 
        }

        return $fpcTags;
    }

    /**
     * @param array $fpcTags
     * @return void
     */
    public function clearCacheByTags($fpcTags)
    {
        self::getCacheInstance()->getFrontend()->clean('matchingAnyTag', $fpcTags);
        /*@fpc cache clean*/ //FPC create flush cache log in that place where FPC use this function

        return $this;
    }

    /**
     * @param string $cacheId
     * @return void
     */
    public function clearCacheById($cacheId)
    {
        $cache = self::getCacheInstance();
        $cache->remove($cacheId);
        /*@fpc cache clean*/  //FPC create flush cache log in that place where FPC use this function

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onXtentoStockupdate($observer)
    {
        $tags = array();
        if ($modifiedStockItems = $observer->getEvent()->getModifiedStockItems()) {
            foreach ($modifiedStockItems as $stockItem) {
                $tags[] = Mirasvit_Fpc_Model_Config::CATALOG_PRODUCT_TAG . $stockItem;
                $tags = $this->getCategoryTagsOnXtentoStockupdate($tags, $stockItem);
            }
        }
        if ($tags) {
            $this->clearCacheByTags($tags);
            if (Mage::getSingleton('fpc/config')->isDebugFlushCacheLogEnabled()) {
                Mage::log('FPC flush cache using event xtento_stockimport_stockupdate_after', null, Mirasvit_Fpc_Model_Config::DEBUG_FLUSH_CACHE_LOG, true);
            }
        }
    }

    /**
     * @param array $tags
     * @return array
     */
    public function getCategoryTagsOnXtentoStockupdate($tags, $productId)
    {
        $product = Mage::getModel('catalog/product');
        $product->setId($productId);
        $categoryIds = $product->getResource()->getCategoryIds($product);
        foreach ($categoryIds as $categoryId) {
            if ($categoryId > 2) {
                $tags[] = Mirasvit_Fpc_Model_Config::CATALOG_CATEGORY_TAG . $categoryId;
            }
        }

        return $tags;
    }
}
