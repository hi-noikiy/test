<?php

/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 30/03/2017
 * Time: 18:50
 */
namespace Beeketing\MagentoCommon\Manager;


use Beeketing\MagentoCommon\Data\Api;
use Beeketing\MagentoCommon\Libraries\SettingHelper;

class CollectManager
{
    /**
     * @var mixed
     */
    private $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $visibility;

    /**
     * CollectManager constructor.
     */
    public function __construct() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resourceConnection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $this->visibility = $objectManager->get('\Magento\Catalog\Model\Product\Visibility');
    }

    /**
     * @return mixed
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @param $name
     * @return mixed
     */
    private function getTableName($name)
    {
        return $this->resourceConnection->getTableName($name);
    }

    /**
     * Count collects
     */
    public function getCollectsCount()
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $select = $this->getConnection()->select()
            ->from($this->getTableName('catalog_category_product_index'), 'COUNT(*)')
            ->where('store_id = ?', $storeId)
            ->where('visibility IN (?)', $this->visibility->getVisibleInSiteIds());
        $result = $this->getConnection()->fetchOne($select);

        return (int) $result;
    }

    /**
     * Count collects by collection id
     *
     * @param $collectionId
     * @return int
     */
    public function getCollectsCountByCollectionId($collectionId)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $select = $this->getConnection()->select()
            ->from($this->getTableName('catalog_category_product_index'), 'COUNT(*)')
            ->where('category_id=?', $collectionId)
            ->where('store_id = ?', $storeId)
            ->where('visibility IN (?)', $this->visibility->getVisibleInSiteIds());
        $result = $this->getConnection()->fetchOne($select);

        return (int) $result;
    }

    /**
     * Count collects by product id
     * @param $productId
     * @return int
     */
    public function getCollectsCountByProductId($productId)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $select = $this->getConnection()->select()
            ->from($this->getTableName('catalog_category_product_index'), 'COUNT(*)')
            ->where('product_id=?', $productId)
            ->where('store_id = ?', $storeId)
            ->where('visibility IN (?)', $this->visibility->getVisibleInSiteIds());
        $result = $this->getConnection()->fetchOne($select);

        return (int) $result;
    }

    /**
     * Get collects
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getCollects($page = Api::PAGE, $limit = Api::ITEM_PER_PAGE)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $select = $this->getConnection()->select()
            ->from($this->getTableName('catalog_category_product_index'))
            ->where('store_id = ?', $storeId)
            ->where('visibility IN (?)', $this->visibility->getVisibleInSiteIds())
            ->limitPage($page, $limit);
        $result = $this->getConnection()->fetchAll($select);

        $results = array();
        if ($result) {
            foreach ($result as $item) {
                $results[] = $this->formatCollect($item);
            }
        }

        return $results;
    }

    /**
     * Get collects by collection id
     * @param $collectionId
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getCollectsByCollectionId($collectionId, $page = Api::PAGE, $limit = Api::ITEM_PER_PAGE)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $select = $this->getConnection()->select()
            ->from($this->getTableName('catalog_category_product_index'))
            ->where('category_id=?', $collectionId)
            ->where('store_id = ?', $storeId)
            ->where('visibility IN (?)', $this->visibility->getVisibleInSiteIds())
            ->limitPage($page, $limit);
        $result = $this->getConnection()->fetchAll($select);

        $results = array();
        if ($result) {
            foreach ($result as $item) {
                $results[] = $this->formatCollect($item);
            }
        }

        return $results;
    }

    /**
     * Get collects by product id
     * @param $productId
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getCollectsByProductId($productId, $page = Api::PAGE, $limit = Api::ITEM_PER_PAGE)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $select = $this->getConnection()->select()
            ->from($this->getTableName('catalog_category_product_index'))
            ->where('product_id=?', $productId)
            ->where('store_id = ?', $storeId)
            ->where('visibility IN (?)', $this->visibility->getVisibleInSiteIds())
            ->limitPage($page, $limit);
        $result = $this->getConnection()->fetchAll($select);

        $results = array();
        if ($result) {
            foreach ($result as $item) {
                $results[] = $this->formatCollect($item);
            }
        }

        return $results;
    }

    /**
     * Format collect
     *
     * @param $collect
     * @return array
     */
    private function formatCollect($collect)
    {
        return array(
            'id' => $collect['category_id'] * 100000 + $collect['product_id'] + $collect['store_id'],
            'collection_id' => (int)$collect['category_id'],
            'product_id' => (int)$collect['product_id'],
            'position' => (int)$collect['position'],
        );
    }
}