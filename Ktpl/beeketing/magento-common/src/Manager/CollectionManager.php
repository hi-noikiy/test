<?php
/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 30/03/2017
 * Time: 19:00
 */

namespace Beeketing\MagentoCommon\Manager;


use Beeketing\MagentoCommon\Data\Api;
use Beeketing\MagentoCommon\Libraries\SettingHelper;

class CollectionManager
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    private $categoryUrlPathGenerator;

    /**
     * CollectionManager constructor.
     */
    public function __construct() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->collectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $this->categoryUrlPathGenerator = $objectManager->get('\Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator');
    }

    /**
     * Count collections
     *
     * @return mixed
     */
    public function getCollectionsCount()
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $result = $this->collectionFactory->create();
        $result->addFieldToFilter('path', ['neq' => '1/2']);
        $result->addIsActiveFilter();
        $result->setStoreId($storeId);

        return $result->getSize();
    }

    /**
     * Get collection by id
     *
     * @param $id
     * @return array
     */
    public function getCollectionById($id)
    {
        $result = $this->collectionFactory->create();
        $result->addAttributeToSelect('*');
        $result->addIdFilter($id);
        $result->addFieldToFilter('path', ['neq' => '1/2']);
        $result->addIsActiveFilter();

        if ($result->getSize()) {
            return $this->formatCollection($result->getFirstItem());
        }

        return [];
    }

    /**
     * Get collections
     *
     * @param $title
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getCollections($title, $page = Api::PAGE, $limit = Api::ITEM_PER_PAGE)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $result = $this->collectionFactory->create();
        $result->addAttributeToSelect('*');
        $result->addFieldToFilter('path', array('neq' => '1/2'));
        $result->addIsActiveFilter();
        $result->setStoreId($storeId);
        $result->addOrder('entity_id');

        // Filter by title
        if ($title) {
            $result->addFieldToFilter('name', array('like' => '%' . $title . '%'));
        }

        // Page
        if ($page) {
            $result->setCurPage($page);
        }

        // Limit
        if ($limit) {
            $result->setPageSize($limit);
        }

        $results = array();
        if ($result->getSize()) {
            foreach ($result as $item) {
                $results[] = $this->formatCollection($item);
            }
        }

        return $results;
    }

    /**
     * Format collection
     *
     * @param $collection
     * @return array
     */
    public function formatCollection(\Magento\Catalog\Model\Category $collection)
    {
        return array(
            'id' => (int)$collection->getId(),
            'title' => $collection->getName(),
            'handle' => $this->categoryUrlPathGenerator->getUrlPathWithSuffix($collection, null),
            'published_at' => $collection->getCreatedAt(),
            'image_url' => $collection->getImageUrl(),
        );
    }
}