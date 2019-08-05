<?php
namespace Ktpl\General\Helper;

use Magento\Store\Model\Group;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const KTPL_BD = "ktpl_business_development/bd_general/bd_file_upload";

    protected $_storeManager;

    protected $scopeConfig;

    protected $customerSession;

    protected $eavModel;

    protected $categoryRepository;

    protected $_promoFactory;
    protected $_banner;
    //protected $_ratingFactory;
    protected $_reviewCollectionFactory;

    protected $productRepository;

    protected $httpHeader;

    protected $directory_list;
    protected $csvProcessor;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Amasty\Promo\Model\RuleFactory $ruleFactory,
        \Amasty\Promo\Block\Banner $banner,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\File\Csv $csvProcessor,
        //\Magento\Review\Model\ResourceModel\Review\CollectionFactory $ratingFactory,
        \Krish\CriticReview\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\Pricing\Render $render

    )
    {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->eavModel = $eavModel;
        $this->timezone = $timezone;
        $this->datetime = $datetime;
        $this->categoryRepository = $categoryRepository;
        $this->_promoFactory = $ruleFactory;
        $this->_banner = $banner;
        $this->directory_list = $directory_list;
        $this->csvProcessor   = $csvProcessor;
        //$this->_ratingFactory = $ratingFactory;
        $this->_reviewCollectionFactory = $reviewCollectionFactory;
        $this->filterProvider = $filterProvider;
        $this->productRepository = $productRepository;
        $this->httpHeader = $httpHeader;
        $this->render = $render;
    }

    public function getProductPrice($sku)
    {

    try{
        $product = $this->productRepository->get($sku);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

    }
        $priceRender = $this->render->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->render->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    public function isMobile()
    {
        $userAgent = $this->httpHeader->getHttpUserAgent();
        return \Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
    }

    public function loadbysku($sku)
    {

        try
        {
           return $this->productRepository->get($sku);
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $e){

           return false;
        }

    }

    public function getPageFilter($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }

    public function getRatingCollections($zeusId){

        $collection = $this->_reviewCollectionFactory->create();

        $joinConditions = 'main_table.review_id = krish_product_attachment_rel.review_id';

        $collection->getSelect()
            ->join(
                 ['krish_product_attachment_rel'],
                 $joinConditions,
                 []
            )
            ->columns("krish_product_attachment_rel.product_id")
            ->where("krish_product_attachment_rel.product_id='".$zeusId."'")
            ->where("main_table.visibility=1");

        return ($collection->getData());
    }

    // public function getRatingCollection($product){

    //     $id = trim($product->getZeusProductId());
    //     $collection = $this->_ratingFactory->create()
    //     ->addStatusFilter(
    //         \Magento\Review\Model\Review::STATUS_APPROVED
    //     )->addEntityFilter(
    //         'product',
    //         $id
    //     )->setDateOrder();

    //     return $collection->getData();
    // }

    /**
     * Get list of Locale for all stores
     * @return array
     */
    public function getListLocaleForAllStores()
    {
        //Locale code
        $locale = [];
        $stores = $this->_storeManager->getStores($withDefault = false);

        //Try to get list of locale for all stores;
        foreach($stores as $store) {
            $locale[] = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getStoreId());
        }
        return $stores;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
                $config_path,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
    }

    public function getConfigValue($config_path,$id)
    {
        return $this->scopeConfig->getValue($config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $id);
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }

      public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    public function getStoreUrl($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getBaseUrl();
    }

    public function getAttributeDefaultLable($attributeId)
    {
        $attr = $this->eavModel->load($attributeId);
        return $attr->getFrontendLabel();
    }

    public function isSale($_product)
    {

        $isSale=0;
        $currentDate=$this->datetime->date($this->timezone->date()->format('Y-m-d H:i:s'));
        if ($_product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        {
            $productTypeInstance = $_product->getTypeInstance();
            $usedProducts = $productTypeInstance->getUsedProducts($_product);
            foreach ($usedProducts as $child)
            {

                $regularPrice = $child->getPriceInfo()->getPrice('regular_price')->getValue();
                $finalPrice = $child->getPriceInfo()->getPrice('final_price')->getValue();
                if ($finalPrice < $regularPrice) {
                    $config_sale = true;
                    $isSale=1;
                    break;
                }
            }
        } else
        {
            $simpleRegularPrice = $_product->getPriceInfo()->getPrice('regular_price')->getValue();
            $simpleFinalPrice = $_product->getPriceInfo()->getPrice('final_price')->getValue();
            if ($simpleFinalPrice < $simpleRegularPrice) {
                $isSale=1;
            }
        }
        return $isSale;
    }

    public function isCategoryUrl($cat_id)
    {

        $category = $this->categoryRepository->get($cat_id, $this->_storeManager->getStore()->getId());

        return $category->getUrl();

    }

    public function giftProductList($_item)
    {

        $rule_id=$this->_banner->getListProductBasedValidRuleIds($_item);
        if($rule_id){

            return $rule_id;

        }else{
            return array();
        }

    }

    /* All active gift sku */

    public function getRuleIdList(){

         $giftRuleId=array();
        $collection = $this->_promoFactory->create()->getCollection()->addFieldToSelect(array('sku','salesrule_id'));
         $joinConditions = 'main_table.salesrule_id = salesrule.rule_id';
        $collection->getSelect()->join(
         ['salesrule'],
         $joinConditions,
         []
        )->columns(array("salesrule.rule_id","salesrule.is_active"))
          ->where("salesrule.is_active=1");

        $gitftproductStr='';
            foreach ($collection as $key => $giftproduct)
            {
                    $giftRuleId[]=$giftproduct->getSalesruleId();
            }

        return array_unique($giftRuleId);
     }

     public function isLoggedIn() {
        if( $this->_customerSession->isLoggedIn() ) {
            return true;
        }
        else {
            return false;
        }
     }

     public function DisplayDiscount($_product) {

        try {

            $productType = $_product->getTypeId();
            switch ($productType) {
                case 'configurable':
                    $regularPrice  = $_product->getPriceInfo()->getPrice('regular_price');
                    $originalPrice = (string) $regularPrice->getMinRegularAmount();
                    $finalPrice    = $_product->getFinalPrice();
                    break;

                default:
                    $originalPrice = $_product->getPrice();
                    $finalPrice = $_product->getFinalPrice();
                    break;
            }

            $percentage = 0;
            if ( (float) $originalPrice > (float) $finalPrice) {
                $percentage = number_format(($originalPrice - $finalPrice) * 100 / $originalPrice,0);
            }
            if ($percentage) {
                return $percentage.__('% Off');
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getbdcontent() {
        $result     = array();
        try {
            $storeId    = $this->getCurrentStoreId();
            $filePath   = $this->getConfigValue(self::KTPL_BD, $storeId);
            $mediaPath  = $this->directory_list->getPath('media').'/import/';
            $file       = $mediaPath.''.$filePath;
            $bdCSV      = $this->csvProcessor->getData($file);
            foreach ($bdCSV as $value) {
                $result[] = addslashes($value[0]);
            }
        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

}
?>