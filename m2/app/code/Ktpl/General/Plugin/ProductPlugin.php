<?php

namespace Ktpl\General\Plugin;
use \Magento\Store\Model\StoreManagerInterface; 
class ProductPlugin
{

    protected $productRepository;
    protected $storeManager;
    protected $_catalogDesign;
    public function __construct(
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
     StoreManagerInterface $storeManager,
     \Magento\Catalog\Model\Design $catalogDesign
    ) {
         $this->productRepository = $productRepository;
         $this->storeManager = $storeManager;
         $this->_catalogDesign = $catalogDesign;
      }

     /**
     * Custom fix for https://github.com/magento/magento2/issues/7710
     * It allows to use a custom them for product page
     * @see \Magento\Catalog\Controller\Product\View::execute
     * @param View $subject
     */
    public function beforeExecute(\Magento\Catalog\Controller\Product\View $subject)
    {
        $productId = (int) $subject->getRequest()->getParam('id');
        $categoryId = (int) $subject->getRequest()->getParam('category', false);
        $product = $this->initProduct($productId, $categoryId);

        if($product) {
            $settings = $this->_catalogDesign->getDesignSettings($product);
            if ($settings->getCustomDesign()) {
                $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }
        }
    }

    /**
     * Get the product with the given ID, and optionally set the category, if given
     * @param $productId
     * @param $categoryId
     * @return \Magento\Catalog\Model\Product|ProductInterface|bool
     */
    protected function initProduct($productId, $categoryId)
    {

        try {
            $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

       /* try {
            $category = $this->categoryRepository->get($categoryId);
            $product->setCategory($category);
        } catch (NoSuchEntityException $e) {
            // Do nothing
        }*/

        return $product;
    }    
}