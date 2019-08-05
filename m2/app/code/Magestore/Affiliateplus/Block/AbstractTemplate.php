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
namespace Magestore\Affiliateplus\Block;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Block BlockTest
 */
class AbstractTemplate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_libDirectory;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magestore\AffiliateplusLevel\Helper\Data
     */
    protected $_helperLevel;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;
    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $_formatLocale;
    /**
     * @var \Magestore\Affiliateplus\Helper\Payment\Tax
     */
    protected $_taxHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_sessionCheckout;
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Magestore\Affiliateplus\Model\AccountFactory
     */
    protected $_accountFactory;
    /**
     * @var \Magestore\Affiliateplus\Helper\Payment
     */
    protected $_paymentHelper;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_helperDirectory;
    /**
     * @var Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected  $_regionCollectionFactory;
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;
    /**
     * @var \Magento\Customer\Model\AddressFatory
     */
    protected $_addressFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_sessionCustomer;
    /**
     * @var \Magestore\Affiliateplus\Model\Session
     */
    protected $_sessionModel;
    /**
     * @var \Magestore\Affiliateplus\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Magento\Framework\CurrencyInterface
     */
    protected $_currencyInterface;
    /**
     * @var \Magestore\Affiliateplus\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_pageModel;
    /**
     * @var \Magestore\Affiliateplus\Helper\Config
     */
    protected $_configHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestInterface;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;
    /**
     * @var
     */
    protected $_payment_method;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /**
     * Block constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Affiliateplus\Helper\Config $configHelper,
        \Magestore\Affiliateplus\Helper\Data $dataHelper,
        \Magestore\Affiliateplus\Helper\Account $accountHelper,
        \Magento\Cms\Model\Page $pageModel,
//        \Magento\Framework\CurrencyInterface $currencyInterface,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magestore\Affiliateplus\Model\Session $sessionModel,
        \Magento\Customer\Model\Session $sessionCustomer,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Helper\Data $helperDirectory,
        \Magestore\Affiliateplus\Helper\Payment $paymentHelper,
        \Magestore\Affiliateplus\Model\AccountFactory $accountFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magestore\Affiliateplus\Helper\Payment\Tax $taxHelper,
        \Magento\Checkout\Model\Session $sessionCheckout,
        \Magento\Framework\Locale\Format $formatLocale,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        array $data = array()
    ) {

        $this->_session = $context->getSession();
        $this->_eventManager = $context->getEventManager();
        $this->_requestInterface = $context->getRequest();
        $this->_configHelper = $configHelper;
        $this->_dataHelper = $dataHelper;
        $this->_accountHelper = $accountHelper;
        $this->_pageModel = $pageModel;
        $this->_storeManager = $context->getStoreManager();
//        $this->_currencyInterface = $currencyInterface;
        $this->_sessionModel = $sessionModel;
        $this->_sessionCustomer = $sessionCustomer;
        $this->_addressFactory = $addressFactory;
        $this->_blockFactory = $blockFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_paymentHelper = $paymentHelper;
        $this->_accountFactory = $accountFactory;
        $this->_priceCurrency = $priceCurrency;
        $this->_taxHelper = $taxHelper;
        $this->_sessionCheckout = $sessionCheckout;
        $this->_formatLocale = $formatLocale;
        $this->_jsonHelper = $jsonHelper;
        $this->_sessionQuote = $sessionQuote;
        $this->_registry = $registry;
        $this->_objectManager = $objectManager;
        $this->_resource = $resourceConnection;
        $this->_libDirectory = $context->getFilesystem()->getDirectoryRead(DirectoryList::LIB_WEB);
        $this->_sessionCheckout = $checkoutSession;
        $this->_configCacheType = $configCacheType;
        $this->_filterProvider =  $filterProvider;
        $this->_resultPageFactory = $resultPageFactory;

        try {
            $this->_currencyInterface = $this->_objectManager->get('\Magento\Framework\CurrencyInterface');
        } catch (\Zend_Currency_Exception $e) {
            $this->_currencyInterface = $localeCurrency->getCurrency($directoryHelper->getBaseCurrencyCode());
        }

        parent::__construct($context, $data);
    }
    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }
    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_getSession()->getStore();
    }

    /**
     * @param $value
     * @return float
     */
    public function convertPrice($value, $format = true)
    {
        return $this->_priceCurrency->convert($value, $format);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function formatPrice($value)
    {
        $value = $this->convertPrice($value,true);
        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }
    //Gin add bug request amount dont convert price
    public function formatPriceNoConvertPrice($value)
    {

        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }

    public function formatPriceAmount($value)
    {
        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
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
     * get Base Url Media
     *
     * @return mixed
     */
    public function getBaseUrlMedia()
    {
        return $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param $store
     * @return mixed
     */
    public function getResponsiveEnable($store)
    {
        return $this->_configHelper->getResponsiveEnable($store);
    }
}
