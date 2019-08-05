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
 * @package   mirasvit/module-rma
 * @version   2.0.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Block\Rma\View;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Items extends \Magento\Framework\View\Element\Template
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Service\Config\RmaRequirementConfig $config,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rmaSearchManagement   = $rmaSearchManagement;
        $this->config                = $config;
        $this->itemFactory           = $itemFactory;
        $this->registry              = $registry;
        $this->imageHelper           = $imageHelper;
        $this->rmaItemHtml           = $rmaItemHtml;
        $this->itemManagement        = $itemManagement;
        $this->itemProductManagement = $itemProductManagement;
        $this->productFactory        = $productFactory;
        $this->context               = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isReasonAllowed()
    {
        return $this->config->isCustomerReasonRequired();
    }

    /**
     * @return bool
     */
    public function isConditionAllowed()
    {
        return $this->config->isCustomerConditionRequired();
    }

    /**
     * @return bool
     */
    public function isResolutionAllowed()
    {
        return $this->config->isCustomerResolutionRequired();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getItems()
    {
        return $this->rmaSearchManagement->getRequestedItems($this->getRma());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @param string                               $imageId
     * @param array                                $attributes
     * @return \Magento\Catalog\Helper\Image
     */
    public function initImage($item, $imageId, $attributes = [])
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        $item->setProductOptions($orderItem->getProductOptions());
        $options = $item->getProductOptions();
        if (!empty($options['simple_sku'])) {
            $childItem = $this->itemFactory->create()->setSku($options['simple_sku']);
            $product   = $this->getProduct($childItem);
            $image     = $this->imageHelper->init($product, $imageId, $attributes);
            if ($image->getUrl() == $image->getDefaultPlaceholderUrl()) {//if child does not have img, use parent
                $product = $this->getProduct($item);
            }
        } else {
            $product = $this->getProduct($item);
        }
        $image = $this->imageHelper->init($product, $imageId, $attributes);
        if ($image->getUrl() == $image->getDefaultPlaceholderUrl()) {
            $product = $this->productFactory->create();
            if (!empty($options['super_product_config'])) {//configurable product
                $product->getResource()->load($product, $options['super_product_config']['product_id']);
            } elseif (!empty($options['info_buyRequest']) && isset($options['info_buyRequest']['product'])) {//others
                $product->getResource()->load($product, $options['info_buyRequest']['product']);
            }
        }

        return $this->imageHelper->init($product, $imageId, $attributes);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct($item)
    {
        return $this->itemProductManagement->getProduct($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemLabel(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemLabel($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemSku(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemSku($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemWeight(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $weight = $this->getProduct($item)->getWeight() * $item->getQtyRequested();

        if (!$weight) {
            $weight = '--';
        }

        return $weight;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getReasonName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemManagement->getReasonName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getConditionName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemManagement->getConditionName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getResolutionName(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemManagement->getResolutionName($item);
    }
}
