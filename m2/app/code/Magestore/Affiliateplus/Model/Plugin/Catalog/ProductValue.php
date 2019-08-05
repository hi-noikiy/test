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
namespace Magestore\Affiliateplus\Model\Plugin\Catalog;

class ProductValue
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_configHelper;
    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var \Magestore\Affiliateplus\Observer\AbtractObserver
     */
    protected $_abtractObserver;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magestore\Affiliateplus\Helper\Cookie
     */
    protected $_helperCookie;
    /**
     * Product constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magestore\Affiliateplus\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magestore\Affiliateplus\Helper\Config $configHelper,
        \Magestore\Affiliateplus\Helper\Data $dataHelper,
        \Magestore\Affiliateplus\Observer\AbtractObserver $abtractObserver,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magestore\Affiliateplus\Helper\Cookie $helperCookie
    ) {
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_configHelper = $configHelper;
        $this->_dataHelper = $dataHelper;
        $this->_abtractObserver = $abtractObserver;
        $this->_eventManager = $eventManager;
        $this->_helperCookie = $helperCookie;
    }

    /**
     * @param \Magento\Catalog\Pricing\Price\BasePrice $object
     * @param $price
     * @return $this|float|int
     */
    public function afterGetValue(\Magento\Catalog\Pricing\Price\BasePrice $object, $price)
    {
        if(!$this->_dataHelper->isAffiliateModuleEnabled()){
            return $price;
        }
        if ($this->_configHelper->getDiscountConfig('type_discount') == 'cart'){
            return $price;
        }
        $affiliateInfo = $this->_helperCookie->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info){
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        }
        if (!$account){
            return $price;
        }

        $discountedObj = new \Magento\Framework\DataObject(
            [
                'price' => $price,
                'discounted' => false,
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_product_get_final_price',
            [
                'product' => $object->getProduct(),
                'discounted_obj' => $discountedObj,
            ]
        );

        if ($discountedObj->getDiscounted()){
            return $discountedObj->getPrice();
        }
        $price = $discountedObj->getPrice();
        $discountType = $this->_configHelper->getDiscountConfig('discount_type');
        $discountValue = $this->_configHelper->getDiscountConfig('discount');
        if ($this->_helperCookie->getNumberOrdered()) {
            if ($this->_configHelper->getDiscountConfig('use_secondary')) {
                $discountType = $this->_configHelper->getDiscountConfig('secondary_type');
                $discountValue = $this->_configHelper->getDiscountConfig('secondary_discount');
            }
        }
        if ($discountType == 'fixed' || $discountType == 'cart_fixed'
        ) {
            $price -= floatval($discountValue);
        } elseif ($discountType == 'percentage') {
            $price -= floatval($discountValue) / 100 * $price;
        }
        if ($price < 0){
            return 0;
        }
        return $price;
    }

}