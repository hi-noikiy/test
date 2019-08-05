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
namespace Magestore\Affiliateplus\Block\ReferFriend\Product;

/**
 * Class Product
 * @package Magestore\Affiliateplus\Block\ReferFriend
 */
class Refer extends \Magestore\Affiliateplus\Block\AbstractTemplate{

    /**
     * @var \Magestore\Affiliateplus\Helper\Url
     */
    protected $_urlHelper;

    /**
     * Set default template for this block
     */
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('Magestore_Affiliateplus::referfriend/product/refer.phtml');
        $this->setData('generate_javascript', true);
    }

    /**
     * @return bool
     */
    public function getGenerateJavascript() {
        if ($this->getData('generate_javascript')) {
            $this->setData('generate_javascript', false);
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getJsonEmail() {
        $result = array(
            'yahoo' => "http://mail.yahoo.com",
            'gmail' => "http://gmail.com",
            'hotmail'	=> "http://hotmail.com",
        );
        return Zend_Json::encode($result);
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        $product = $this->getCurrentProduct();
        if($product){
            return $product;
        }
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
     * @return mixed
     */
    protected function _getHelperUrl(){
        if(!$this->_urlHelper){
            $this->_urlHelper = $this->_objectManager->create('Magestore\Affiliateplus\Helper\Url');
        }
        return $this->_urlHelper;
    }

    /**
     * @return mixed
     */
    public function getShowListRefer(){
        return $this->_configHelper->getReferConfig('show_list_refer');
    }
}