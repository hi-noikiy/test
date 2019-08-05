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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Items extends \Magento\Backend\Block\Template
{

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Core\Helper\Image $imageHelper,
        \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Helper\Item\Option $rmaItemOption,
        \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->imageHelper            = $imageHelper;
        $this->itemListBuilder        = $itemListBuilder;
        $this->itemManagement         = $itemManagement;
        $this->itemQuantityManagement = $itemQuantityManagement;
        $this->itemProductManagement  = $itemProductManagement;
        $this->rmaManagement          = $rmaManagement;
        $this->rmaItemOption          = $rmaItemOption;
        $this->rmaItemHtml            = $rmaItemHtml;
        $this->itemFactory            = $itemFactory;
        $this->productFactory         = $productFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->getData('rma');
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->rmaManagement->getOrder($this->getRma());
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRmaItems()
    {
        $rma = $this->getRma();
        if ($rma->getId()) {
            return $this->itemListBuilder->getRmaItems($rma);
        } else {
            return $this->itemListBuilder->getList($this->getOrder());
        }
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
            if ($image->isImagePlaceholder()) {//if child does not have img, use parent
                $product = $this->getProduct($item);
            }
        } else {
            $product = $this->getProduct($item);
        }
        $image = $this->imageHelper->init($product, $imageId, $attributes);
        if ($image->isImagePlaceholder()) {
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
    public function getProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item)
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
    public function getOrderItemPrice(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemPrice($item, $this->getOrder());
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getConditionList()
    {
        return $this->rmaItemOption->getConditionList();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getResolutionList()
    {
        return $this->rmaItemOption->getResolutionList();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getReasonList()
    {
        return $this->rmaItemOption->getReasonList();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return boolean|int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIsBundleItem(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return false;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyStock(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyStock($item->getProductId());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyOrdered(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyOrdered($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyAvailable(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyAvailable($item);
    }
}