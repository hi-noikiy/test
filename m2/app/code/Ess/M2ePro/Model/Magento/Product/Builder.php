<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Magento\Product;

class Builder extends \Ess\M2ePro\Model\AbstractModel
{
    /** @var \Magento\Framework\Filesystem\DriverPool  */
    protected $driverPool;
    /** @var \Magento\Framework\Filesystem  */
    protected $filesystem;
    /** @var \Magento\Store\Model\StoreFactory  */
    protected $storeFactory;
    /** @var StockItem\Factory  */
    protected $stockItemFactory;
    /** @var \Magento\Catalog\Model\Product\Media\Config  */
    protected $productMediaConfig;
    /** @var \Magento\Catalog\Model\ProductFactory  */
    protected $productFactory;
    /** @var \Ess\M2ePro\Helper\Factory  */
    protected $helperFactory;
    /** @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor  */
    protected $indexStockProcessor;
    /** @var \Magento\CatalogInventory\Api\StockConfigurationInterface  */
    protected $stockConfiguration;
    /** @var \Magento\Catalog\Model\Product */
    private $product;
    /** @var \Magento\CatalogInventory\Model\Stock\Item */
    private $stockItem;

    //########################################

    public function __construct(
        \Magento\Framework\Filesystem\DriverPool $driverPool,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $indexStockProcessor,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Ess\M2ePro\Model\Magento\Product\StockItem\Factory $stockItemFactory
    )
    {
        $this->driverPool           = $driverPool;
        $this->filesystem           = $filesystem;
        $this->storeFactory         = $storeFactory;
        $this->stockItemFactory     = $stockItemFactory;
        $this->productMediaConfig   = $productMediaConfig;
        $this->productFactory       = $productFactory;
        $this->helperFactory        = $helperFactory;
        $this->indexStockProcessor  = $indexStockProcessor;
        $this->stockConfiguration   = $stockConfiguration;
        parent::__construct(
            $helperFactory,
            $modelFactory
        );
    }

    //########################################

    public function getProduct()
    {
        return $this->product;
    }

    //########################################

    public function buildProduct()
    {
        $this->createProduct();
        $this->createStockItem();

        /*
         * Since version 2.1.8 Magento performs check if there is a record for product in table
         * cataloginventory_stock_status during quantity validation. Force reindex for new product will be helpful
         * if scheduled reindexing is enabled for stock status.
         */
        if ($this->indexStockProcessor->isIndexerScheduled() && $this->product->getId()) {
            $this->indexStockProcessor->reindexRow($this->product->getId(), true);
        }
    }

    private function createProduct()
    {
        $this->product = $this->productFactory->create();
        $this->product->setTypeId(\Ess\M2ePro\Model\Magento\Product::TYPE_SIMPLE_ORIGIN);
        $this->product->setAttributeSetId($this->productFactory->create()->getDefaultAttributeSetId());

        // ---------------------------------------

        $this->product->setName($this->getData('title'));
        $this->product->setDescription($this->getData('description'));
        $this->product->setShortDescription($this->getData('short_description'));
        $this->product->setSku($this->getData('sku'));

        // ---------------------------------------

        $this->product->setPrice($this->getData('price'));
        $this->product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $this->product->setTaxClassId($this->getData('tax_class_id'));
        $this->product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        // ---------------------------------------

        $websiteIds = array();
        if (!is_null($this->getData('store_id'))) {
            $store = $this->storeFactory->create()->load($this->getData('store_id'));
            $websiteIds = array($store->getWebsiteId());
        }

        if (empty($websiteIds)) {
            $websiteIds = array($this->helperFactory->getObject('Magento\Store')->getDefaultWebsiteId());
        }

        $this->product->setWebsiteIds($websiteIds);

        // ---------------------------------------

        $gallery = $this->makeGallery();

        if (count($gallery) > 0) {
            $firstImage = reset($gallery);
            $firstImage = $firstImage['file'];

            $this->product->setData('image', $firstImage);
            $this->product->setData('thumbnail', $firstImage);
            $this->product->setData('small_image', $firstImage);

            $this->product->setData('media_gallery', array(
                'images' => $gallery,
                'values' => array(
                    'main'        => $firstImage,
                    'image'       => $firstImage,
                    'small_image' => $firstImage,
                    'thumbnail'   => $firstImage
                )
            ));
        }

        // ---------------------------------------

        $this->product->getResource()->save($this->product);
    }

    //########################################

    private function createStockItem()
    {
        $stockItem = $this->stockItemFactory
                          ->create(
                              $this->product->getId(),
                              $this->stockConfiguration->getDefaultScopeId()
                          );
        $stockItem->setProduct($this->product);

        $stockItem->addData(array(
            'qty'                         => $this->getData('qty'),
            'stock_id'                    => \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID,
            'website_id'                  => $this->stockConfiguration->getDefaultScopeId(),
            'is_in_stock'                 => 1,
            'use_config_min_qty'          => 1,
            'use_config_min_sale_qty'     => 1,
            'use_config_max_sale_qty'     => 1,
            'is_qty_decimal'              => 0,
            'use_config_backorders'       => 1,
            'use_config_notify_stock_qty' => 1
        ));

        $stockItem->save();
    }

    private function makeGallery()
    {
        if (!is_array($this->getData('images')) || count($this->getData('images')) == 0) {
            return array();
        }

        $fileDriver = $this->driverPool->getDriver(\Magento\Framework\Filesystem\DriverPool::FILE);
        $tempMediaPath = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath()
        . $this->productMediaConfig->getBaseTmpMediaPath() . DIRECTORY_SEPARATOR;

        $gallery = array();
        $imagePosition = 1;

        foreach ($this->getData('images') as $tempImageName) {
            if (!$fileDriver->isFile($tempMediaPath . $tempImageName)) {
                continue;
            }

            $gallery[] = array(
                'file'     => $tempImageName,
                'label'    => '',
                'position' => $imagePosition++,
                'disabled' => 0
            );
        }

        return $gallery;
    }

    //########################################
}