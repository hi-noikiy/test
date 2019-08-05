<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Block\Product\View;

use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\StoreCredit\Model\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Tax\Pricing\Adjustment;

/**
 * Class Aheadworks\StoreCredit\Block\Product\View\Discount
 */
class Discount extends \Magento\Framework\View\Element\Template
{
    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Aheadworks_StoreCredit::product/view/discount.phtml';

    /**
     * @var CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditService;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     * @param Session $customerSession
     * @param Config $config
     * @param PriceHelper $priceHelper
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerStoreCreditManagementInterface $customerStoreCreditService,
        Session $customerSession,
        Config $config,
        PriceHelper $priceHelper,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->customerStoreCreditService = $customerStoreCreditService;
        $this->customerSession = $customerSession;
        $this->priceHelper = $priceHelper;
        $this->productRepository = $productRepository;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Is ajax request or not
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->_request->isAjax();
    }

    /**
     * Retrieve config value for Display prices discounted by available store credit
     *
     * @return boolean
     */
    public function isDisplayBlock()
    {
        return $this->config->isDisplayPriceWithDiscount();
    }

    /**
     * Check for display only available store credit
     *
     * @return boolean
     */
    public function isDisplayOnlyAvailableStoreCredit()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getProduct();

        if ($product->getOptions()) {
            return true;
        } elseif ($product->hasCustomOptions()) {
            return true;
        } elseif ($product->getFinalPrice() == 0) {
            return true;
        } elseif ($product->getTypeId() == ConfigurableType::TYPE_CODE) {
            return true;
        } elseif ($product->getTypeId() == BundleType::TYPE_CODE) {
            return true;
        }

        return false;
    }

    /**
     * Get customer available store credit
     *
     * @return int
     */
    public function getAvailableStoreCredit()
    {
        if ($this->customerSession->getId()) {
            return $this->customerStoreCreditService->getCustomerStoreCreditBalance($this->customerSession->getId());
        }

        return 0;
    }

    /**
     * Get customer available amount
     *
     * @return float
     */
    private function getAvailableAmount()
    {
        $storeCredit = $this->getAvailableStoreCredit();
        if ($storeCredit > 0) {
            return $storeCredit;
        }
        return 0;
    }

    /**
     * Get formatted customer available amount
     *
     * @return string
     */
    public function getFormattedAvailableAmount()
    {
        return $this->priceHelper->currency($this->getAvailableAmount(), true, false);
    }

    /**
     * Get product price with discount
     *
     * @return float
     */
    public function getPriceWithDiscount()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getProduct();
        $discount = $this->getAvailableAmount();
        $productPrice = $product->getPriceInfo()
            ->getPrice(BasePrice::PRICE_CODE)->getAmount()->getValue(Adjustment::ADJUSTMENT_CODE);
        $salePrice = $productPrice - $discount;

        if ($discount > 0) {
            if ($salePrice > 0) {
                return $salePrice;
            } else {
                return 0;
            }
        }

        return $productPrice;
    }

    /**
     * Get formatted product price with discount
     *
     * @return string
     */
    public function getFormattedPriceWithDiscount()
    {
        return $this->priceHelper->currency($this->getPriceWithDiscount());
    }

    /**
     * Retrieve current product
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct()
    {
        return $this->productRepository->getById($this->_request->getParam('id'));
    }
}
