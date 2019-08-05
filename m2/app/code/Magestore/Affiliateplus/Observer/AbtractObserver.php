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
namespace Magestore\Affiliateplus\Observer;

class AbtractObserver {
    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_helperConfig;

    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magestore\Affiliateplus\Helper\Cookie
     */
    protected $_helperCookie;

    /**
     * @var \Magestore\Affiliateplus\Helper\Account
     */
    protected $_helperAccount;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magestore\Affiliateplus\Model\AccountFactory
     */
    protected $_accountFactory;

    /**
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory
     */
    protected $_accountCollectionFactory;

    /**
     * @var \Magestore\Affiliateplus\Model\Session
     */
    protected $_affiliateSession;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store Manager
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_sessionManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_requestHttp;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_backendQuoteSession;


    /**
     * ProductGetFinalPrice constructor.
     * @param \Magestore\Affiliateplus\Helper\Config $helperConfig
     * @param \Magestore\Affiliateplus\Helper\Data $helper
     * @param \Magestore\Affiliateplus\Helper\Cookie|\Magestore\Affiliateplus\Helper\Data $helperCookie
     * @param \Magestore\Affiliateplus\Helper\Account $helperAccount
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magestore\Affiliateplus\Model\AccountFactory $accountFactory
     * @param \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory
     * @param \Magestore\Affiliateplus\Model\Session $affiliateSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Checkout\Model\Session $sessionManager
     */
    public function __construct(
        \Magestore\Affiliateplus\Helper\Config $helperConfig,
        \Magestore\Affiliateplus\Helper\Data $helper,
        \Magestore\Affiliateplus\Helper\Cookie $helperCookie,
        \Magestore\Affiliateplus\Helper\Account $helperAccount,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\Affiliateplus\Model\AccountFactory $accountFactory,
        \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \Magestore\Affiliateplus\Model\Session $affiliateSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Request\Http $requestHttp,
        \Magento\Checkout\Model\Session $sessionManager,
        \Magento\Backend\Model\Session\Quote $backendSessionQuote,
         \Magento\Framework\Message\ManagerInterface $managerInterface
    )
    {
        $this->_helperConfig = $helperConfig;
        $this->_helper = $helper;
        $this->_helperCookie = $helperCookie;
        $this->_helperAccount = $helperAccount;
        $this->_request = $request;
        $this->_accountFactory = $accountFactory;
        $this->_accountCollectionFactory = $accountCollectionFactory;
        $this->_affiliateSession = $affiliateSession;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_sessionManager = $sessionManager;
        $this->_requestHttp = $requestHttp;
        $this->messageManager = $managerInterface;
        $this->_backendQuoteSession = $backendSessionQuote;
    }

    /**
     * Check the available affiliate by account code from url
     * @return bool
     */
    public function _checkAffiliateParam(){
        $accountCode = $this->_request->getParam('acc');
        if (!$accountCode || ($accountCode == '')) {
            $paramList = $this->_helperConfig->getReferConfig('url_param_array');
            $paramArray = explode(',', $paramList);
            for ($i = (count($paramArray) - 1); $i >= 0; $i--) {
                $accountCode = $this->_request->getParam($paramArray[$i]);
                if ($accountCode && ($accountCode != ''))
                    break;
            }
        }
        if($this->_helperConfig->getGeneralConfig('url_param_value') == 2){
            $account = $this->_accountFactory->create()
                ->load($accountCode, 'account_id');
        } else{
            $account = $this->_accountFactory->create()
                ->load($accountCode, 'identify_code');
        }
        if ($account->getId()){
            $accountCode = $account->getIdentifyCode();
        }
        if (!$accountCode){
            return false;
        }
        if ($account = $this->_affiliateSession->getAccount()){
            if ($account->getIdentifyCode() == $accountCode){
                return false;
            }
        }
        $account = $this->_accountFactory->create()->loadByIdentifyCode($accountCode);

        if (!$account->getId()){
            return false;
        }

        return true;
    }

    /**
     * Calculate the final price to product
     * @param $product
     * @param $price
     * @return float|int
     */

    public function _getFinalPrice($product, $price) {
        $discountedObj = new \Magento\Framework\DataObject(
            [
                'price' => $price,
                'discounted' => false,
            ]
        );

        $this->_eventManager->dispatch('affiliateplus_product_get_final_price',
            [
                'product' => $product,
                'discounted_obj' => $discountedObj,
            ]
        );

        if ($discountedObj->getDiscounted())
            return $discountedObj->getPrice();
        $price = $discountedObj->getPrice();

        $discountType = $this->_getConfigHelper()->getDiscountConfig('discount_type');
        $discountValue = $this->_getConfigHelper()->getDiscountConfig('discount');
        if ($this->_helperCookie->getNumberOrdered()) {
            if ($this->_getConfigHelper()->getDiscountConfig('use_secondary')) {
                $discountType = $this->_getConfigHelper()->getDiscountConfig('secondary_type');
                $discountValue = $this->_getConfigHelper()->getDiscountConfig('secondary_discount');
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

    /**
     * @return \Magestore\Affiliateplus\Helper\Config
     */
    public function _getConfigHelper(){
        return $this->_helperConfig;
    }
}