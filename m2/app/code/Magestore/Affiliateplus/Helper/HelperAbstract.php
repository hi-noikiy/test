<?php

/**
 * Magestore
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

namespace Magestore\Affiliateplus\Helper;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class HelperAbstract extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;
    /**
     * @var \Magento\Framework\Simplexml\Config
     */
    protected $_config;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManagerInterface;
    /**
     * @var \Magento\Framework\CurrencyInterface
     */
    protected $_currencyInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Cookie Manager Interface
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * Public Cookie Metadata
     * @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata
     */
    protected $_publicCookieMetadata;

    /**
     * Cookie metadata factory
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * Affiliate Model Account Factory
     * @var \Magestore\Affiliateplus\Model\AccountFactory
     */
    protected $_accountFactory;

    /**
     * Affiliate Account Collection Factory
     * @var \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory
     */
    protected $_accountCollectionFactory;

    /**
     * Request Interface
     * @var \Magento\Framework\App\RequestInterface $request
     */
    protected $_request;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_backendQuoteSession;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    /**
     * @var \Magestore\Affiliateplus\Model\Session
     */
    protected $_sessionModel;

    /**
     * @var
     */
    protected $_backendUrl;
    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    protected $_curlFactory;
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    protected $_storeFactory;

    /**
     * Block constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magestore\Affiliateplus\Model\Session $sessionModel,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManagerInterface,
        \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $publicCookieMetadata,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magestore\Affiliateplus\Model\AccountFactory $accountFactory,
        \Magestore\Affiliateplus\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManagerInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Simplexml\Config $config,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Directory\Helper\Data $directoryHelper


    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_productFactory = $productFactory;
        $this->_moduleManager = $context->getModuleManager();
        $this->_sessionModel = $sessionModel;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_cookieManager = $cookieManagerInterface;
        $this->_publicCookieMetadata = $publicCookieMetadata;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_accountFactory = $accountFactory;
        $this->_accountCollectionFactory = $accountCollectionFactory;
        $this->_request = $context->getRequest();
        $this->_sessionManagerInterface = $sessionManagerInterface;
        $this->_checkoutSession = $checkoutSession;
        $this->_backendQuoteSession = $backendQuoteSession;
        $this->_config = $config;
        $this->_layout = $layout;
        $this->_assetRepo = $assetRepo;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_priceCurrency = $priceCurrency;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_localeDate = $localeDate;
        $this->_curlFactory = $curlFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_filesystem = $filesystem;
        try {
            $this->_currencyInterface = $this->_objectManager->get('\Magento\Framework\CurrencyInterface');
        } catch (\Zend_Currency_Exception $e) {
            $this->_currencyInterface = $localeCurrency->getCurrency($directoryHelper->getBaseCurrencyCode());
        }

    }

    /**
     * get store config
     *
     * @param $key
     * @param null $store
     * @return mixed
     */
    public function getConfig($key, $store = null) {
        return $this->_scopeConfig->getValue(
            $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check the module is enable or not
     * @param $moduleName Magestore_Affiliateplus
     * @return bool
     */
    public function isModuleEnabled($moduleName){
        return $this->_moduleManager->isEnabled($moduleName);
    }

    /**
     * @param $value
     * @param bool|true $format
     * @param null $currency
     * @return float|string
     */
    public function convertCurrency($value, $format = true, $currency = null)
    {
        return $format ? $this->_priceCurrency->convertAndFormat(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore(),
            $currency
        ) : $this->_priceCurrency->convert($value, $this->getStore(), $currency);
    }
    /**
     * Get store view ID
     * @param null $storeId
     * @return int
     */
    public function getStoreViewId($storeId = null){
        return $this->_storeManager->getStore($storeId)->getId();
    }

    /**
     * Get store view
     * @param null $storeId
     * @return int
     */
    public function getStore($storeId = null){
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * getURL
     * @param $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    /**
     * Check if the current store is admin or not
     * @return bool
     */
    public function isAdmin(){
        $appState = $this->_objectManager->get('Magento\Framework\App\State');
        if($appState->getAreaCode() ==  \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE){
            return true;
        }
        return false;
    }

    /**
     * @param $value
     * @param bool|true $format
     * @return mixed
     */
    public function convertPrice($value, $format = true, $store = null)
    {
        if(!$store){
            $store = $this->getStore();
        }
        return $this->_priceCurrency->convert($value, $store);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formatPrice($value)
    {
        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }

    /**
     * Prepare date for save in DB
     *
     * string format used from input fields (all date input fields need apply locale settings)
     * int value can be declared in code (this meen whot we use valid date)
     *
     * @param string|int|\DateTime $date
     * @return string
     */
    public function formatDateToSaveInDB($date, $format = 'Y-m-d H:i:s'){
        if (empty($date)) {
            return null;
        }
        // unix timestamp given - simply instantiate date object
        if (is_scalar($date) && preg_match('/^[0-9]+$/', $date)) {
            $date = (new \DateTime())->setTimestamp($date);
        } elseif (!($date instanceof \DateTime)) {
            // normalized format expecting Y-m-d[ H:i:s]  - time is optional
            $date = new \DateTime($date);
        }
        return $date->format($format);
    }

    /**
     * Retrieve formatting date
     * @reference vendor\magento\framework\View\Element\AbstractBlock.php
     * @param null|string|\DateTime $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * Retrieve formatting time
     *
     * @param   \DateTime|string|null $time
     * @param   int $format
     * @param   bool $showDate
     * @return  string
     */
    public function formatTime(
        $time = null,
        $format = \IntlDateFormatter::SHORT,
        $showDate = false
    ) {
        $time = $time instanceof \DateTimeInterface ? $time : new \DateTime($time);
        return $this->_localeDate->formatDateTime(
            $time,
            $showDate ? $format : \IntlDateFormatter::NONE,
            $format
        );
    }

    /**
     * Get Current logged in account
     * @return mixed
     */
    public function getAffiliateAccount() {
        return $this->getAffiliateSession()->getAccount();
    }

    /**
     * Get Affiliate Session
     * @var \Magestore\Affiliateplus\Model\Session
     */
    public function getAffiliateSession(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Session');
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession(){
        return $this->_checkoutSession;
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote
     */
    public function getBackendSessionQuote(){
        return $this->_backendQuoteSession;
    }

    /**
     * @return mixed
     */
    public function getBackendUrl(){
        if(!$this->_backendUrl){
            $this->_backendUrl = $this->_objectManager->create('Magento\Backend\Helper\Data')->getHomePageUrl();
        }
        return $this->_backendUrl;
    }

    /**
     * @param null $domain
     * @param string $path
     * @return \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata
     */
    public function getPublicCookieMetadata($domain=null, $path='/'){
        $publicCookie = $this->_publicCookieMetadata;
        if($domain){
            $publicCookie->setDomain($domain);
        }
        if($path){
            $publicCookie->setPath($path);
        }
        return $publicCookie;
    }

    /**
     * render price to order currency
     * @param type $value
     * @param type $store
     * @return string
     */
    public function renderCurrency($value, $store) {
        $baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $currentCurrencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        if ($baseCurrencyCode == $currentCurrencyCode)
            return '';
        else
            return '<br/>[' . $this->formatPrice($value) . ']';
    }
}