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



class Mirasvit_Fpc_Model_Observer_MassSaveCacheClear extends Mirasvit_Fpc_Model_Observer_CacheClear
{

    /**
     * Flush cache after mass product save if use minimal set of tags
     *
     * @param Varien_Event_Observer $e
     * @return void
     */
    public function updateCache($e)
    {
        if ($this->_cacheTagsLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL
            && $this->_cacheTagsLevel != Mirasvit_Fpc_Model_Config::CACHE_TAGS_LEVEL_MINIMAL_PREFIX) {
            return true;
        }

        $productIds = $e->getProductIds();
        $tags = array();

        if (!$productIds) {
            return true;
        }

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('catalog_category_product');

        $select = $readConnection->select()
            ->from($table, array(
                'category_id',
            ))->where('product_id IN (?)', $productIds)
            ->group('category_id');

        if ($categoryRowSet = $readConnection->fetchCol($select)) {
            $categoryTags = function(&$id) { $id = Mirasvit_Fpc_Model_Config::CATALOG_CATEGORY_TAG . $id; };
            array_walk_recursive($categoryRowSet, $categoryTags);
            $tags = array_merge($tags, $categoryRowSet);
        }

        foreach ($productIds as $productId) {
            $tags[] = $this->_prefix . Mirasvit_Fpc_Model_Config::CATALOG_PRODUCT_TAG . $productId;
        }

        if ($tags) {
            $this->_cache->clearCacheByTags($tags);
        }

        if (Mage::helper('core')->isModuleEnabled('Mirasvit_AsyncCache')) {
            Mage::getSingleton('asynccache/handler')->processQueue();
        }
    }
}
