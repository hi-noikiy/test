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



abstract class Mirasvit_Fpc_Model_Observer_CacheClear
{
    /**
     * @var int
     */
    protected $_cacheTagsLevel;

    protected $_prefix;

    protected $_cache;

    public function __construct()
    {
        $config = Mage::getSingleton('fpc/config');
        $this->_cacheTagsLevel = $config->getCacheTagslevelLevel();
        $this->_prefix = $config->getTagPrefix();
        $this->_cache = Mage::getSingleton('fpc/cache');
    }

    /**
     * Get category tags for current product
     *
     * @param object $product
     * @param array $tags
     * @return array
     */
    public function getCategoryTags($product, $tags)
    {
        if (!is_object($product)) {
            return $tags;
        }
        $categoryIds = $product->getCategoryIds();
        foreach ($categoryIds as $categoryId) {
            $tags[] = $this->_prefix . Mirasvit_Fpc_Model_Config::CATALOG_CATEGORY_TAG . $categoryId;
        }
        return $tags;
    }
}
