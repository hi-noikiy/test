<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Block\ReferFriend;

/**
 * Class Product
 * @package Magestore\Affiliateplus\Block\ReferFriend
 */
class Product extends \Magestore\Affiliateplus\Block\AbstractTemplate{

    /**
     * @var
     */
    protected $_storeViewId;

    /**
     * @var \Magestore\Affiliateplus\Helper\Url
     */
    protected $_urlHelper;

    /**
     * @return int
     */
    public function getStoreViewId(){
        if(!$this->_storeViewId){
            $this->_storeViewId = $this->_storeManager->getStore()->getId();
        }
        return $this->_storeViewId;
    }

    /**
     * @return bool|mixed
     */
    public function isEnableShareFriend()
    {
        if ($this->_accountHelper->accountNotLogin()) {
            return false;
        }
        return $this->_configHelper->getReferConfig('refer_enable_product_detail', $this->getStoreViewId());
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->_objectManager->get('Magento\Framework\Registry')->registry('product');
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getAffiliateUrl($product)
    {
        $productUrl = $product->getProductUrl();
        return $this->_getHelperUrl()->addAccToUrl($productUrl);
    }

    /**
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getShareIconsHtml($product) {
        $block = $this->getLayout()->getBlockSingleton('Magestore\Affiliateplus\Block\ReferFriend\Product\Refer');
        $block->setProduct($product);
        return $block->toHtml();
    }

    /**
     * @return mixed
     */
    protected function _getHelperUrl(){
        if(!$this->_urlHelper){
            $this->_urlHelper = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Url');
        }
        return $this->_urlHelper;
    }
}