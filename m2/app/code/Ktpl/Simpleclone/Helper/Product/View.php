<?php
namespace Ktpl\Simpleclone\Helper\Product;

use Magento\Framework\View\Result\Page as ResultPage;

/**
 * Catalog category helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends \Magento\Catalog\Helper\Product\View
{
    protected $_banner;
      /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param array $messageGroups
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        \Amasty\Promo\Block\Banner $banner,
        array $messageGroups = []
    ) {
        $this->_banner = $banner;
        parent::__construct($context,$catalogSession,$catalogDesign,$catalogProduct,$coreRegistry,$messageManager,$categoryUrlPathGenerator,[]);
    }

    /**
     * Init layout for viewing product page
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Framework\DataObject $params
     * @return \Magento\Catalog\Helper\Product\View
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initProductLayout(ResultPage $resultPage, $product, $params = null)
    {
        $settings = $this->_catalogDesign->getDesignSettings($product);
        $pageConfig = $resultPage->getConfig();

        if ($settings->getCustomDesign()) {
            $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
        }

        // Apply custom page layout
        if ($settings->getPageLayout()) {
            $pageConfig->setPageLayout($settings->getPageLayout());
        }

        $urlSafeSku = rawurlencode($product->getSku());

        // Load default page handles and page configurations
        if ($params && $params->getBeforeHandles()) {
            foreach ($params->getBeforeHandles() as $handle) {
                $resultPage->addPageLayoutHandles(['id' => $product->getId(), 'sku' => $urlSafeSku], $handle);
                $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], $handle, false);
            }
        }

        $resultPage->addPageLayoutHandles(['id' => $product->getId(), 'sku' => $urlSafeSku]);
        $isHasGift=$this->isGiftProduct($product);
        
        /*if($product->getTypeId()=='simple' && $isHasGift){
            $customTemplateName='simple_clone';
        	$resultPage->addPageLayoutHandles(['type' => $customTemplateName], null, false);
        }else{*/
        	$resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], null, false);	
        //}
        

        if ($params && $params->getAfterHandles()) {
            foreach ($params->getAfterHandles() as $handle) {
                $resultPage->addPageLayoutHandles(['id' => $product->getId(), 'sku' => $urlSafeSku], $handle);
                $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], $handle, false);
            }
        }

        // Apply custom layout update once layout is loaded
        $update = $resultPage->getLayout()->getUpdate();
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates) {
            if (is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }

        $currentCategory = $this->_coreRegistry->registry('current_category');
        $controllerClass = $this->_request->getFullActionName();
        if ($controllerClass != 'catalog-product-view') {
            $pageConfig->addBodyClass('catalog-product-view');
        }
        $pageConfig->addBodyClass('product-' . $product->getUrlKey());
        if ($currentCategory instanceof \Magento\Catalog\Model\Category) {
            $pageConfig->addBodyClass('categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($currentCategory))
                ->addBodyClass('category-' . $currentCategory->getUrlKey());
        }

        return $this;
    }

    public function isGiftProduct($product){
        $result=0;
        $rules=$this->_banner->getValidRules();
        $ruledata=$rules->getData();
        if(isset($ruledata[0]['sku']) && $ruledata[0]['sku']!=''){
            $result=1;  
            $this->_coreRegistry->register('gift_product', 1); 
        }else{
            $this->_coreRegistry->register('gift_product', 0); 
        }
        return $result;
    }

    public function checkGiftProduct()
    {
         return $this->_coreRegistry->registry('gift_product');
    }

}