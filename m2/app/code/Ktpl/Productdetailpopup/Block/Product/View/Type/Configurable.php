<?php
/**
 * Catalog super product configurable part block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Productdetailpopup\Block\Product\View\Type;

use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Configurable extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct = null;

    /**
     * Current customer
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * Prices
     *
     * @var array
     */
    protected $_prices = [];

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data $imageHelper
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ConfigurableAttributeData
     */
    protected $configurableAttributeData;

    /**
     * @var Format
     */
    private $localeFormat;

    /**
     * @var Session
     */
    private $customerSession;
    
    protected $_productCollectionFactory;
    protected $_categoryFactory;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\ConfigurableProduct\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param array $data
     * @param Format|null $localeFormat
     * @param Session|null $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,    
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Ktpl\Productdetailpopup\Helper\Data $helper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItemRepository,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        array $data = [],
        Format $localeFormat = null,
        Session $customerSession = null
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->catalogProduct = $catalogProduct;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_productRepository = $productRepository;
        $this->currentCustomer = $currentCustomer;
        $this->configurableAttributeData = $configurableAttributeData;
        $this->localeFormat = $localeFormat ?: ObjectManager::getInstance()->get(Format::class);
        $this->customerSession = $customerSession ?: ObjectManager::getInstance()->get(Session::class);

        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }

    /**
     * Get cache key informative items.
     *
     * @return array
     * @since 100.2.0
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }
    
    public function getZeusProductCollection()
    {
        $categories = [31];//category ids array
        //$category = $this->_categoryFactory->create()->load($categoryId);
        $pcollection = $this->_productCollectionFactory->create();
        $pcollection->addAttributeToSelect('*');
        //$pcollection->addCategoryFilter($category);
        $pcollection->addCategoriesFilter(['in' => $categories]);
        $pcollection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $pcollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $pcollection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $pcollection->setPageSize(3);
        $pcollection->getSelect()->orderRand();
        return $pcollection;
    }

    public function getAllowProducts(\Magento\Catalog\Model\Product $currentProduct)
    {
        if (!$this->hasAllowProducts()) {
            $products = [];
            $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();
            $allProducts = $currentProduct->getTypeInstance(true)->getUsedProducts($currentProduct, null);

            foreach ($allProducts as $product) {
                if ($product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $product;
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }
    
    public function getJsonConfig1(\Magento\Catalog\Model\Product $currentProduct)
    {
        $relatedProducts = $currentProduct->getRelatedProducts();
        $store = $this->getCurrentStore();
        $ass_Products = [];
        $zeus_collection = $this->getZeusProductCollection();
        foreach($zeus_collection as $zeus){
            $qty = $this->_stockItemRepository->getStockQty($zeus->getId());
            if ($zeus->isSaleable() && $qty > 0) {
                $zeus_Products[] = array('id' => $zeus->getId(),
                    'name' => $zeus->getName(),
                    'price' => $zeus->getFinalPrice(),
                    'sku' => $zeus->getSku());
            }    
        }
        
        foreach ($relatedProducts as $relatedProduct) {
            $qty = $this->_stockItemRepository->getStockQty($relatedProduct->getId());
            if ($relatedProduct->isSaleable() && $qty > 0) {
                $product = $this->_productRepository->getById($relatedProduct->getId());
                $relatedproductId = $relatedProduct->getId();
                $relatedproductName = $product->getName();
                $relatedproductPrice = $this->getProductPrice($product);
                $ass_Products[] = array('id' => $relatedProduct->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getFinalPrice(),
                    'sku' => $relatedProduct->getSku());
            }
        }

        $config_product[] = array('id' => $currentProduct->getId(),
                'name' => $currentProduct->getName(),
                'price' => $this->getProductPrice($currentProduct),
                'sku' => $currentProduct->getSku(),
                'image' => $currentProduct->getImage(),
                'description' => $currentProduct->getShortDescription()
            );

        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');
        if($currentProduct->getData('type_id') == 'configurable') {
            $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts($currentProduct));
            $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);
            
            $config = [
                'attributes' => $attributesData['attributes'],
                'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
                'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
                'optionPrices' => $this->getOptionPrices(),
                'priceFormat' => $this->localeFormat->getPriceFormat(),
                'prices' => [
                    'oldPrice' => [
                        'amount' => $this->localeFormat->getNumber($regularPrice->getAmount()->getValue()),
                    ],
                    'basePrice' => [
                        'amount' => $this->localeFormat->getNumber($finalPrice->getAmount()->getBaseAmount()),
                    ],
                    'finalPrice' => [
                        'amount' => $this->localeFormat->getNumber($finalPrice->getAmount()->getValue()),
                    ],
                ],
                'productId' => $currentProduct->getId(),
                'relatedProduct' => $ass_Products,
                'zeus_product' => $zeus_Products,
                'chooseText' => __('Choose an Option...'),
                'config_product' => $config_product,
                'images' => $this->getOptionImages(),
                'swatchimage'=> $options['swatch'],
                'index' => isset($options['index']) ? $options['index'] : [],
            ];
        } else {
            $relatedProducts = $currentProduct->getRelatedProducts();
            $rel_Products = [];
            foreach ($relatedProducts as $relatedProduct) {
                $qty = $this->_stockItemRepository->getStockQty($relatedProduct->getId());
                if ($relatedProduct->isSaleable() && $qty > 0) {
                    $product = $this->_productRepository->getById($relatedProduct->getId());
                    $relatedproductId = $relatedProduct->getId();
                    $relatedproductName = $product->getName();
                    $relatedproductPrice = $this->getProductPrice($product);
                    $rel_Products[] = array('id' => $relatedProduct->getId(),
                        'name' => $product->getName(),
                        'price' => $product->getFinalPrice(),
                        'showprice' => $this->getProductPrice($product),
                        'sku' => $relatedProduct->getSku());
                }
            }

            $simple_product[] = array('id' => $currentProduct->getId(),
                'name' => $currentProduct->getName(),
                'price' => $currentProduct->getFinalPrice(),
                'showprice' => $this->getProductPrice($currentProduct),
                'sku' => $currentProduct->getSku(),
                'image' => $currentProduct->getImage(),
                'description' => $currentProduct->getShortDescription()
            );
            $config = [
                'simple_product' => $simple_product,
                'relatedProduct' => $rel_Products,
                'zeus_product' => $zeus_Products,
            ];
        }
        
        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->jsonEncoder->encode($config);
    }

    protected function _getAdditionalConfig()
    {
        return [];
    }

     public function getStockItem($productId)
    {
        return $this->_stockItemRepository->get($productId);
    }

    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }
}
