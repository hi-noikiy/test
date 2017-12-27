<?php
/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 31/03/2017
 * Time: 17:17
 */

namespace Beeketing\MagentoCommon\Manager;


use Beeketing\MagentoCommon\Libraries\Helper;
use Magento\Catalog\Model\Product\Visibility;

class VariantManager
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $configurable;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * VariantManager constructor.
     */
    public function __construct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->objectManager = $objectManager;
        $this->productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $this->configurable = $objectManager->get('\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable');
        $this->stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $this->productRepository = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');
    }

    /**
     * Get variants by product
     *
     * @param $product
     * @return array
     */
    public function getVariantsByProduct(\Magento\Catalog\Model\Product $product)
    {
        $variants = array();

        $productTypeInstance = $product->getTypeInstance();
        if ($productTypeInstance->getChildrenIds($product->getId())) {
            $usedProducts = $productTypeInstance->getUsedProducts($product);
        } else {
            $usedProducts[] = $product;
        }

        foreach ($usedProducts as $variant) {
            $variants[] = $this->formatVariant($variant, $product);
        }

        return $variants;
    }

    /**
     * Get variant by id
     * @param $id
     * @param $productId
     * @return array
     */
    public function getVariantById($id, $productId)
    {
        $result = $this->productCollectionFactory->create();
        $result->addAttributeToSelect('*');
        $result->addIdFilter($id);

        if ($result->getSize()) {
            $product = $this->productRepository->getById($productId);
            return $this->formatVariant($result->getFirstItem(), $product);
        }

        return [];
    }

    /**
     * Format variant
     *
     * @param $variant
     * @return array
     */
    public function formatVariant(\Magento\Catalog\Model\Product $variant, \Magento\Catalog\Model\Product $product = null)
    {
        // If beeketing variant
        if (strpos($variant->getSku(), '_BEEKETING-') !== false) {
            preg_match('/(_BEEKETING-)(\d+)-(\d+)$/', $variant->getSku(), $skuMatches);
            if (isset($skuMatches[3]) && is_numeric($skuMatches[3])) {
                $product = $this->productRepository->getById($skuMatches[3]);
            }
        }

        if ($product) {
            $productId = $product->getId();
        } else {
            $productIds = $this->configurable->getParentIdsByChild($variant->getId());
            $productId = array_shift($productIds);
            $product = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addIdFilter($productId)
                ->getFirstItem();
        }

        $stock = $this->stockRegistry->getStockItem($variant->getId());

        // Get attributes
        $attributes = array();
        $productTypeInstance = $product->getTypeInstance();
        if ($productTypeInstance->getChildrenIds($product->getId())) {
            $superAttributes = $productTypeInstance->getConfigurableAttributesAsArray($product);
            foreach ($superAttributes as $id => $attribute) {
                if ($attributeData = $variant->getCustomAttribute($attribute['attribute_code'])) {
                    $attributes[$id] = $attributeData->getValue();
                }
            }
        }

        $variantImage = $variant->getMediaGalleryImages() ? $variant->getMediaGalleryImages()->getFirstItem() : '';
        $result = array(
            'id' => (int)$variant->getId(),
            'product_id' => (int)$productId,
            'barcode' => '',
            'image_id' => $variantImage ? (int)$variantImage->getId() : '',
            'title' => $variant->getName(),
            'price' => Helper::formatPrice($variant->getFinalPrice()),
            'price_compare' => $variant->getPrice() > $variant->getFinalPrice() ?
                Helper::formatPrice($variant->getPrice()) : '',
            'option1' => $variant->getName(),
            'option2' => '',
            'option3' => '',
            'grams' => '',
            'position' => '',
            'sku' => $variant->getSku(),
            'inventory_management' => $stock->getManageStock(),
            'inventory_policy' => $stock->getBackorders(),
            'inventory_quantity' => $stock->getQty(),
            'fulfillment_service' => '',
            'weight' => $variant->getWeight(),
            'weight_unit' => '',
            'requires_shipping' => '',
            'taxable' => '',
            'updated_at' => $variant->getUpdatedAt(),
            'created_at' => $variant->getCreatedAt(),
            'in_stock' => $stock->getIsInStock(),
            'attributes' => $attributes,
        );

        return $result;
    }

    /**
     * Create variant
     *
     * @param $productId
     * @param $content
     * @return array
     */
    public function createVariant($productId, $content)
    {
        $originId = $content['origin_id'];
        /** @var \Magento\Catalog\Model\Product $variant */
        $variant = $this->productRepository->getById($originId);
        $websiteIds = $variant->getWebsiteIds();
        $variant->setId(null);
        $variant->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);

        // Get sku
        preg_match('/(\(.*)\s(\d+)(\))$/', $content['option1'], $skuMatches);
        $skuPostfix = '_BEEKETING-';
        if (isset($skuMatches[2]) && is_numeric($skuMatches[2])) {
            $skuPostfix .= $skuMatches[2];
        } else {
            $skuPostfix .= substr(hexdec(uniqid()), -3, 3);
        }
        $skuPostfix .= '-' . $productId;

        $variant->setSku($variant->getSku() . $skuPostfix);
        $variant->setData('image', null);
        $variant->setData('media_gallery', null);
        $variant->setName($content['option1']);
        $variant->setPrice($content['price']);

        /** @var \Magento\Catalog\Model\Product $newVariant */
        $newVariant = $this->objectManager->create('\Magento\Catalog\Model\Product');
        $newVariant->setData($variant->getData());
        $newVariant->setStockData(array());
        $newVariant->setWebsiteIds($websiteIds);
        $newVariant->getResource()->save($newVariant);

        $stock = $this->stockRegistry->getStockItem($newVariant->getId());
        $stock->setUseConfigManageStock(false);
        $stock->setManageStock(false);
        $stock->setQty($content['inventory_quantity']);
        $this->stockRegistry->updateStockItemBySku($newVariant->getSku(), $stock);

        return $this->formatVariant($newVariant, $newVariant);
    }

    /**
     * Update variant
     *
     * @param $id
     * @param $productId
     * @param $content
     * @return array
     */
    public function updateVariant($id, $productId, $content)
    {
        /** @var \Magento\Catalog\Model\Product $variant */
        $variant = $this->productRepository->getById($id);

        if (isset($content['price'])) {
            $variant->setPrice($content['price']);
        }

        $variant->getResource()->save($variant);

        $stock = $this->stockRegistry->getStockItem($variant->getId());
        $stock->setQty($content['inventory_quantity']);
        $this->stockRegistry->updateStockItemBySku($variant->getSku(), $stock);

        $product = $this->productRepository->getById($productId);

        return $this->formatVariant($variant, $product);
    }

    /**
     * Delete variant
     *
     * @param $productId
     * @param $variantId
     */
    public function deleteVariant($productId, $variantId)
    {
        $registry = $this->objectManager->get('\Magento\Framework\Registry');
        $registry->register('isSecureArea', true);

        $product = $this->productRepository->getById($variantId);
        $this->productRepository->delete($product);
    }
}