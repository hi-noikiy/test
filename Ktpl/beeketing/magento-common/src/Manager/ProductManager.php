<?php
/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 31/03/2017
 * Time: 13:40
 */

namespace Beeketing\MagentoCommon\Manager;


use Beeketing\MagentoCommon\Data\Api;
use Beeketing\MagentoCommon\Libraries\Helper;
use Beeketing\MagentoCommon\Libraries\SettingHelper;

class ProductManager
{
    /**
     * @var VariantManager
     */
    private $variantManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $visibility;

    public function __construct()
    {
        $this->variantManager = new VariantManager();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $this->resourceConnection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $this->productUrlPathGenerator = $objectManager->get('\Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator');
        $this->visibility = $objectManager->get('\Magento\Catalog\Model\Product\Visibility');
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @param $name
     * @return string
     */
    private function getTableName($name)
    {
        return $this->resourceConnection->getTableName($name);
    }

    /**
     * Count products
     *
     * @return mixed
     */
    public function getProductsCount()
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $result = $this->productCollectionFactory->create();
        $result->addFieldToFilter('type_id', array('nin' => array('bundle', 'grouped')));
        $result->setVisibility($this->visibility->getVisibleInSiteIds());
        $result->addStoreFilter($storeId);

        return $result->getSize();
    }

    /**
     * Get product by id
     *
     * @param $id
     * @return array
     */
    public function getProductById($id)
    {
        $result = $this->productCollectionFactory->create();
        $result->addAttributeToSelect('*');
        $result->addFieldToFilter('type_id', array('nin' => array('bundle', 'grouped')));
        $result->setVisibility($this->visibility->getVisibleInSiteIds());
        $result->addIdFilter($id);

        if ($result->getSize()) {
            $product = $result->getFirstItem();
            return $this->formatProduct($product);
        }

        return [];
    }

    /**
     * Get products
     *
     * @param $title
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getProducts($title = null, $page = Api::PAGE, $limit = Api::ITEM_PER_PAGE)
    {
        $storeId = SettingHelper::getInstance()->getCurrentStoreId();
        $result = $this->productCollectionFactory->create();
        $result->addFieldToFilter('type_id', array('nin' => array('bundle', 'grouped')));
        $result->setVisibility($this->visibility->getVisibleInSiteIds());
        $result->addAttributeToSelect('*');
        $result->addStoreFilter($storeId);
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
            /** @var \Magento\Catalog\Model\Product $item */
            foreach ($result as $item) {
                $results[] = $this->formatProduct($item);
            }
        }

        return $results;
    }

    /**
     * Format product
     *
     * @param $product
     * @return array
     */
    public function formatProduct(\Magento\Catalog\Model\Product $product)
    {
        // Get images
        $productIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $productIds = array_shift($productIds) ?: $productIds;
        array_push($productIds, $product->getId());

        $select = $this->getConnection()->select()
            ->from(array('cpgv' => $this->getTableName('catalog_product_entity_media_gallery_value')), '')
            ->join(array('cpg' => $this->getTableName('catalog_product_entity_media_gallery')), 'cpgv.value_id = cpg.value_id')
            ->where('cpgv.entity_id IN (?)', $productIds)
            ->where('cpgv.disabled = ?', 0);

        $result = $this->getConnection()->fetchAll($select);
        $images = array();
        foreach ($result as $item) {
            $images[] = array(
                'id' => (int)$item['value_id'],
                'src' => Helper::getProductImageUrl($item['value']),
            );
        }

        // Get variants
        $variants = $this->variantManager->getVariantsByProduct($product);

        return array(
            'id' => $product->getId(),
            'published_at' => $product->isAvailable() ? $product->getCreatedAt() : '',
            'handle' => $this->productUrlPathGenerator->getUrlPathWithSuffix($product, null),
            'title' => $product->getName(),
            'vendor' => '',
            'tags' => '',
            'description' => $product->getDescription(),
            'images' => $images,
            'image' => Helper::getProductImageUrl($product->getImage()),
            'variants' => $variants,
        );
    }

    /**
     * Update product
     *
     * @param $id
     * @param $content
     * @return array
     */
    public function updateProduct($id, $content)
    {
        return array();
    }
}