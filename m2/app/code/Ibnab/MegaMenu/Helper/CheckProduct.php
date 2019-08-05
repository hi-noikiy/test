<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ibnab\MegaMenu\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class CheckProduct extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_registry;
    protected $listProduct;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,       
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Block\Product\ListProduct $listProduct 
    )
    {
        $this->_registry = $registry;
        $this->listProduct = $listProduct;
        parent::__construct($context);
    }

    public function getCurrentProduct()
    {
        if($product = $this->_registry->registry('current_product')){
            return $product->getSku();
        }else{
            return false;
        }

    }

    public function getBuyNowUrl()
    {
        $url = "javascript:void(0)";
        $product = $this->_registry->registry('current_product');
        if( $product ){
              $url =  $this->listProduct->getAddToCartUrl($product);
        }
        return $url;
    }
}
