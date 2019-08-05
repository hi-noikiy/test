<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Block\Account;

use Magento\Framework\View\Element\Template;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ProductTypeConfigurable;
use Amasty\Affiliate\Model\Account;

class Widget extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'account/widget.phtml';

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Report\Bestsellers\CollectionFactory
     */
    private $bestsellersCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    private $imageHelperFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductTypeConfigurable
     */
    private $catalogProductTypeConfigurable;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection
     */
    private $bundleSelection;

    /**
     * Catalog product link
     *
     * @var \Magento\GroupedProduct\Model\ResourceModel\Product\Link
     */
    protected $productLinks;

    /**
     * @var \Amasty\Affiliate\Model\Account
     */
    private $account;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * Widget constructor.
     * @param Template\Context $context
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param ProductTypeConfigurable $catalogProductTypeConfigurable
     * @param \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection
     * @param \Magento\GroupedProduct\Model\ResourceModel\Product\Link $catalogProductLink
     * @param Account $account
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Amasty\Affiliate\Model\ResourceModel\Report\Bestsellers\CollectionFactory $bestsellersCollectionFactory
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Magento\Framework\Url\Helper\Data $ulrHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ProductTypeConfigurable $catalogProductTypeConfigurable,
        \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection,
        \Magento\GroupedProduct\Model\ResourceModel\Product\Link $catalogProductLink,
        \Amasty\Affiliate\Model\Account $account,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\Affiliate\Model\ResourceModel\Report\Bestsellers\CollectionFactory $bestsellersCollectionFactory,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Magento\Framework\Url\Helper\Data $ulrHelper,
        array $data = []
    ) {
        $this->imageHelperFactory = $imageHelperFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->bundleSelection = $bundleSelection;
        $this->productLinks = $catalogProductLink;
        $this->account = $account;
        $this->productRepository = $productRepository;
        $this->bestsellersCollectionFactory = $bestsellersCollectionFactory;
        $this->accountRepository = $accountRepository;
        $this->urlHelper = $ulrHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProducts()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addFinalPrice();
        $collection->setOrder('created_at', 'DESC');
        $collection->getSelect()->limit($this->getCurrentAccount()->getWidgetProductsNum());
        $collection->addUrlRewrite();

        if ($this->accountRepository->getCurrentAccount()->getWidgetType() == Account::WIDGET_TYPE_BESTSELLER) {
            /** @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection $collectionBestSellers */
            $collectionBestSellers = $this->bestsellersCollectionFactory->create()->setModel(
                'Magento\Catalog\Model\Product'
            );
            $productIds = $collectionBestSellers->getColumnValues('product_id');
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return $collection;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product $product
     * @return string
     */
    public function getProductImageUrl($product)
    {
        $imageUrl = $this->imageHelperFactory->create()
            ->init($product, 'product_thumbnail_image')->getUrl();

        return $imageUrl;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductUrl($product)
    {
        $configurableProductIds = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getEntityId());
        if (!empty($configurableProductIds)) {
            $product = $this->productRepository->getById($configurableProductIds[0]);
        } else {
            $bundleProductIds = $this->bundleSelection->getParentIdsByChild($product->getEntityId());
            if (!empty($bundleProductIds)) {
                $product = $this->productRepository->getById($bundleProductIds[0]);
            } else {
                $groupedProductIds = $this->productLinks->getParentIdsByChild(
                    $product->getEntityId(),
                    \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
                );
                if (!empty($groupedProductIds)) {
                    $product = $this->productRepository->getById($groupedProductIds[0]);
                }
            }
        }

        $url = $product->getProductUrl();

        $accountCode = $this->accountRepository->getCurrentAccount()->getReferringCode();
        $codeParameter = $this->_scopeConfig->getValue('amasty_affiliate/url/parameter');
        $params = [
            $codeParameter => $accountCode,
            'referring_service' => 'widget'
        ];
        $url = $this->urlHelper->addRequestParam($url, $params);

        return $url;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Interceptor $product
     * @return string
     */
    public function convertToPrice($product)
    {
        $price = $product->getMinimalPrice();

        return $this->account->convertToPrice($price);
    }

    /**
     * @return \Amasty\Affiliate\Api\Data\AccountInterface
     */
    public function getCurrentAccount()
    {
        return $this->accountRepository->getCurrentAccount();
    }

    /**
     * @param $field
     * @return string
     */
    public function checked($field)
    {
        $checked = '';

        if ($this->getCurrentAccount()->getData($field)) {
            $checked = 'checked';
        }

        return $checked;
    }

    /**
     * @return string
     */
    public function checkedBestsellers()
    {
        $checked = '';

        if ($this->getCurrentAccount()->getWidgetType() == Account::WIDGET_TYPE_BESTSELLER) {
            $checked = 'checked';
        }

        return $checked;
    }

    /**
     * @return string
     */
    public function checkedNew()
    {
        $checked = '';

        if ($this->getCurrentAccount()->getWidgetType() == Account::WIDGET_TYPE_NEW) {
            $checked = 'checked';
        }

        return $checked;
    }
}
