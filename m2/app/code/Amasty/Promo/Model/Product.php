<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

class Product
{
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    private $state;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\CatalogInventory\Api\StockStateInterface $state,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param $sku
     *
     * @return bool|float|int
     */
    public function getProductQty($sku)
    {
        $qty = 0;

        try {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $this->productRepository->get($sku, false, $this->storeManager->getStore()->getId());
            if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
                || $product->getTypeId() === \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ) {
                return false;
            }

            $qty = $this->state->getStockQty(
                $product->getId(),
                $this->storeManager->getWebsite()->getId()
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e->getTraceAsString());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e->getTraceAsString());
        }

        return $qty;
    }
}
