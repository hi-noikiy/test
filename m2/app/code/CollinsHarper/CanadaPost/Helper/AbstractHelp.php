<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper;


use \Magento\Shipping\Model\Config;
use \Magento\Store\Model\Information;
use \Magento\Sales\Model\Order\Shipment;
use \Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use \CollinsHarper\Core\Helper\Measure;


/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractHelp extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     *
     * @var shipmeent link
     */
    protected $shipmentLink;


    /**
     *
     * @var string
     */
    protected $error;

    /**
     *
     * @var bool
     */
    private $force_live = false;

    /**
     *
     * @var bool
     */
    private $is_mock = false;

    /**
     *
     * @var Varien_Data
     */
    private $mock_config = false;

    const XML_PATH_PLATFORM_ID = 'carriers/cpcanadapost/platform_id';
    const XML_PATH_PLATFORM_LOGIN = 'carriers/cpcanadapost/platform_api_login';
    const XML_PATH_PLATFORM_PASSWORD = 'carriers/cpcanadapost/platform_api_password';
    const XML_PATH_ACTIVE = 'carriers/cpcanadapost/active';
    const XML_PATH_ACTIVE_CHECKOUT = 'carriers/cpcanadapost/frontend_disabled';
    const XML_PATH_TEST_MODE = 'carriers/cpcanadapost/test';
    const XML_PATH_DEBUG_MODE = 'carriers/cpcanadapost/debug';
    const XML_PATH_CACHE = 'carriers/cpcanadapost/enable_cache';
    const XML_PATH_URL = 'carriers/cpcanadapost/api_url';
    const XML_PATH_API_LOGIN = 'carriers/cpcanadapost/api_login';
    const XML_PATH_API_PASSWORD = 'carriers/cpcanadapost/api_password';
    const XML_PATH_TEST_URL = 'carriers/cpcanadapost/api_test_url';
    const XML_PATH_TEST_API_LOGIN = 'carriers/cpcanadapost/api_test_login';
    const XML_PATH_TEST_API_PASSWORD = 'carriers/cpcanadapost/api_test_password';

    const XML_PATH_CONTRACT_ID = 'carriers/cpcanadapost/contract';
    const XML_PATH_CUSTOMER_NUMBER = 'carriers/cpcanadapost/api_customer_number';
    const XML_PATH_HAS_DEFAULT_CC = 'carriers/cpcanadapost/has_default_credit_card';

    const XML_PATH_ALLOWED_METHODS = 'carriers/cpcanadapost/allowed_methods';
    const XML_PATH_FORCE_FRENCH = 'carriers/cpcanadapost/return_lang';
    const XML_PATH_LOCALE = 'carriers/cpcanadapost/locale';
    const XML_PATH_QUOTE_TYPE = 'carriers/cpcanadapost/quote_type';
    const XML_PATH_DATE_FORMAT = 'carriers/cpcanadapost/date_format';
    const XML_PATH_LEAD_TIME = 'carriers/cpcanadapost/lead_time_days';
    const XML_PATH_BACK_ORDER_NO_ESTIMATE = 'carriers/cpcanadapost/back_order_no_estimate';
    const XML_PATH_SHOW_ESTIMATE_DATE = 'carriers/cpcanadapost/show_delivery_date';
    const XML_PATH_NON_DELIVERY = 'carriers/cpcanadapost/nondelivery_preference';
    const XML_PATH_SIGNATURE = 'carriers/cpcanadapost/require_signature';
    const XML_PATH_SIGNATURE_THRESHOLD = 'carriers/cpcanadapost/signature_threshhold';
    const XML_PATH_COVERAGE = 'carriers/cpcanadapost/require_coverage';
    const XML_PATH_COVERAGE_THRESHOLD = 'carriers/cpcanadapost/coverage_threshhold';
    const XML_PATH_D2PO = 'carriers/cpcanadapost/deliver_to_postoffice';
    const XML_PATH_D2PO_LIST_SIZE = 'carriers/cpcanadapost/postoffice_list_size';
    const XML_PATH_REQUIRE_C4P = 'carriers/cpcanadapost/card_for_pickup';
    const XML_PATH_REQUIRE_DNSD = 'carriers/cpcanadapost/do_not_safe_drop';
    const XML_PATH_REQUIRE_LAD = 'carriers/cpcanadapost/leave_at_door';
    const XML_PATH_NOTIFY_ON_SHIPMENT = 'carriers/cpcanadapost/notify_on_shipment';
    const XML_PATH_NOTIFY_ON_EXCEPTION = 'carriers/cpcanadapost/notify_on_exception';
    const XML_PATH_NOTIFY_ON_DELIVERY = 'carriers/cpcanadapost/notify_on_delivery';
    const XML_PATH_LABEL_FORMAT = 'carriers/cpcanadapost/output_format';
    const XML_PATH_NOTIFY_REASON = 'carriers/cpcanadapost/reason_for_export';
    const XML_PATH_NOTIFY_OTHER_REASON = 'carriers/cpcanadapost/other_reason';

    const XML_PATH_NOTIFY_ERROR_SHOW = 'carriers/cpcanadapost/showmethod';
    const XML_PATH_NOTIFY_ERROR_MESSAGE = 'carriers/cpcanadapost/specificerrmsg';


    const XML_PATH_SIGNUP_URL = 'carriers/cpcanadapost/signup_url_prod';



    const REASON_FOR_EXPORT_OTHER = 'OTH';
    const MAXIMUM_SKU_LENGTH = 43;

    const CP_BILLING_CC = 'CreditCard';
    const CP_BILLING_ACCOUNT = 'Account';

    const CACHE_KEY_PREFIX = 'chcanpost2module_getrates_';

    const CACHE_LIFETIME = 300;


    const DEFAULT_SHIPPING_TITLE = "Standard Shipping";
    const RATE_DOM_EXPRESS = 'DOM.EP';
    const RATE_DOM_RP = 'DOM.RP';
    const API_ENCODING = 'UTF-8';
    const API_METHOD_POST = 'POST';
    const API_METHOD_DELETE = 'DELETE';
    const API_METHOD_DELETE_SUCCESS = 204;
    const API_METHOD_CONNECTION_ERROR = 'connection_error';

    const API_PATH_RATE = '%srs/ship/price';
    const API_PATH_SERVICE = '%srs/ship/service/%s?country=%s';
    const API_PATH_TRACKING = '%svis/track/pin/%s/detail';
    const API_PATH_TRANSMIT = '%srs/%s/%s/manifest';
    const API_PATH_REGISTRATION = '%sot/token/%s';
    const API_PATH_OFFICE = '%srs/postoffice?d2po=true&postalCode=%s&maximum=%s';
    const API_PATH_RETURNS = '%srs/%s/%s/authorizedreturn';

    const API_PATH_CONTRACT_SHIPMENT = '%srs/%s/%s/shipment';
    const API_PATH_NONCONTRACT_SHIPMENT = '%srs/%s/ncshipment';

    const QUOTE_COMMERCIAL = 'commercial';
    const QUOTE_COUNTER = 'counter';


    const RETURN_SERVICE_CODE = 'DOM.EP';
    const DEFAULT_SERVICE_CODE = 'DOM.EP';

    const PREG_MANIFEST = '/manifest/';
    const PREG_POST_OFFICE = '/postoffice/';
    const PREG_RATE_RESPONSE = '/price-quotes/';

    const COUNTRY_CANADA = 'CA';
    const COUNTRY_USA = 'US';
    const CURRENCY_USD = 'USD';
    const CURRENCY_CAD = 'CAD';

    const LANG_MAGE_FR_PART = 'fr_';
    const LANG_MAGE_FR_CA = 'fr_CA';
    const LANG_CP_FR = 'fr-CA';
    const LANG_CP_EN = 'en-CA';
    const LANG_CP_RATE_FR = 'FR';
    const LANG_CP_RATE_EN = 'EN';

    const EN_TEST = 'en';
    const FR_TEST = 'fr';


    const DEMO_ID = ' CPC_DEMO_XML ';


    const DEFAULT_SIZE = 10;


    /**
     * @var \CollinsHarper\Core\Logger\Logger
     */
    protected $_chLogged;

    /**
     *
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;


    /**
     *
     * @var array
     */
    protected $_header_manifest = array('Content-Type: application/vnd.cpc.manifest-v2+xml', 'Accept: application/vnd.cpc.manifest-v2+xml');
    /**
     *
     * @var array
     */
    protected $_header_post_office = array('Accept:application/vnd.cpc.postoffice+xml');
    /**
     *
     * @var array
     */
    protected $_header_rate = array('Content-Type: application/vnd.cpc.ship.rate+xml', 'Accept: application/vnd.cpc.ship.rate+xml');
    /**
     *
     * @var array
     */
    protected $_header_tracking = array('Accept: application/vnd.cpc.track+xml');
    /**
     *
     * @var array
     */
    protected $_header_transmit = array(
        'Content-Type: application/vnd.cpc.manifest-v2+xml',
        'Accept: application/vnd.cpc.manifest-v2+xml'
    );

    /**
     *
     * @var array
     */
    protected $_header_registration = array(
        'Accept: application/vnd.cpc.registration+xml',
        'Content-Type: application/vnd.cpc.registration+xml'
    );

    /**
     *
     * @var array
     */
    protected $_header_return = array(
        'Content-Type: application/vnd.cpc.authreturn+xml',
        'Accept: application/vnd.cpc.authreturn+xml'
    );

    /**
     *
     * @var array
     */
    protected $_header_shipment = array(
        'Content-Type: application/vnd.cpc.shipment-v2+xml',
        'Accept: application/vnd.cpc.shipment-v2+xml'
    );

    /**
     *
     * @var array
     */
    protected $_header_non_shipment = array(
        'Content-Type: application/vnd.cpc.ncshipment+xml',
        'Accept: application/vnd.cpc.ncshipment+xml'
    );

    /**
     *
     * @var array
     */
    protected $_header_pdf = array(
        'Accept:application/pdf'
    );

    /**
     *
     * @var string
     */
    protected $_header_platform_id = 'Platform-Id: ';
    /**
     *
     * @var string
     */
    protected $_header_language = 'Accept-language: ';


    /**
     *
     * @var array
     */
    protected $_currencies_map = array('AF' => 'AFA', 'AL' => 'ALL', 'DZ' => 'DZD', 'AS' => 'USD', 'AD' => 'EUR', 'AO' => 'AOA', 'AI' => 'XCD', 'AQ' => 'NOK', 'AG' => 'XCD', 'AR' => 'ARA', 'AM' => 'AMD', 'AW' => 'AWG', 'AU' => 'AUD', 'AT' => 'EUR', 'AZ' => 'AZM', 'BS' => 'BSD', 'BH' => 'BHD', 'BD' => 'BDT', 'BB' => 'BBD', 'BY' => 'BYR', 'BE' => 'EUR', 'BZ' => 'BZD', 'BJ' => 'XAF', 'BM' => 'BMD', 'BT' => 'BTN', 'BO' => 'BOB', 'BA' => 'BAM', 'BW' => 'BWP', 'BV' => 'NOK', 'BR' => 'BRL', 'IO' => 'GBP', 'BN' => 'BND', 'BG' => 'BGN', 'BF' => 'XAF', 'BI' => 'BIF', 'KH' => 'KHR', 'CM' => 'XAF', 'CA' => 'CAD', 'CV' => 'CVE', 'KY' => 'KYD', 'CF' => 'XAF', 'TD' => 'XAF', 'CL' => 'CLF', 'CN' => 'CNY', 'CX' => 'AUD', 'CC' => 'AUD', 'CO' => 'COP', 'KM' => 'KMF', 'CD' => 'CDZ', 'CG' => 'XAF', 'CK' => 'NZD', 'CR' => 'CRC', 'HR' => 'HRK', 'CU' => 'CUP', 'CY' => 'EUR', 'CZ' => 'CZK', 'DK' => 'DKK', 'DJ' => 'DJF', 'DM' => 'XCD', 'DO' => 'DOP', 'TP' => 'TPE', 'EC' => 'USD', 'EG' => 'EGP', 'SV' => 'USD', 'GQ' => 'XAF', 'ER' => 'ERN', 'EE' => 'EEK', 'ET' => 'ETB', 'FK' => 'FKP', 'FO' => 'DKK', 'FJ' => 'FJD', 'FI' => 'EUR', 'FR' => 'EUR', 'FX' => 'EUR', 'GF' => 'EUR', 'PF' => 'XPF', 'TF' => 'EUR', 'GA' => 'XAF', 'GM' => 'GMD', 'GE' => 'GEL', 'DE' => 'EUR', 'GH' => 'GHC', 'GI' => 'GIP', 'GR' => 'EUR', 'GL' => 'DKK', 'GD' => 'XCD', 'GP' => 'EUR', 'GU' => 'USD', 'GT' => 'GTQ', 'GN' => 'GNS', 'GW' => 'GWP', 'GY' => 'GYD', 'HT' => 'HTG', 'HM' => 'AUD', 'VA' => 'EUR', 'HN' => 'HNL', 'HK' => 'HKD', 'HU' => 'HUF', 'IS' => 'ISK', 'IN' => 'INR', 'ID' => 'IDR', 'IR' => 'IRR', 'IQ' => 'IQD', 'IE' => 'EUR', 'IL' => 'ILS', 'IT' => 'EUR', 'CI' => 'XAF', 'JM' => 'JMD', 'JP' => 'JPY', 'JO' => 'JOD', 'KZ' => 'KZT', 'KE' => 'KES', 'KI' => 'AUD', 'KP' => 'KPW', 'KR' => 'KRW', 'KW' => 'KWD', 'KG' => 'KGS', 'LA' => 'LAK', 'LV' => 'LVL', 'LB' => 'LBP', 'LS' => 'LSL', 'LR' => 'LRD', 'LY' => 'LYD', 'LI' => 'CHF', 'LT' => 'LTL', 'LU' => 'EUR', 'MO' => 'MOP', 'MK' => 'MKD', 'MG' => 'MGF', 'MW' => 'MWK', 'MY' => 'MYR', 'MV' => 'MVR', 'ML' => 'XAF', 'MT' => 'EUR', 'MH' => 'USD', 'MQ' => 'EUR', 'MR' => 'MRO', 'MU' => 'MUR', 'YT' => 'EUR', 'MX' => 'MXN', 'FM' => 'USD', 'MD' => 'MDL', 'MC' => 'EUR', 'MN' => 'MNT', 'MS' => 'XCD', 'MA' => 'MAD', 'MZ' => 'MZM', 'MM' => 'MMK', 'NA' => 'NAD', 'NR' => 'AUD', 'NP' => 'NPR', 'NL' => 'EUR', 'AN' => 'ANG', 'NC' => 'XPF', 'NZ' => 'NZD', 'NI' => 'NIC', 'NE' => 'XOF', 'NG' => 'NGN', 'NU' => 'NZD', 'NF' => 'AUD', 'MP' => 'USD', 'NO' => 'NOK', 'OM' => 'OMR', 'PK' => 'PKR', 'PW' => 'USD', 'PA' => 'PAB', 'PG' => 'PGK', 'PY' => 'PYG', 'PE' => 'PEI', 'PH' => 'PHP', 'PN' => 'NZD', 'PL' => 'PLN', 'PT' => 'EUR', 'PR' => 'USD', 'QA' => 'QAR', 'RE' => 'EUR', 'RO' => 'ROL', 'RU' => 'RUB', 'RW' => 'RWF', 'KN' => 'XCD', 'LC' => 'XCD', 'VC' => 'XCD', 'WS' => 'WST', 'SM' => 'EUR', 'ST' => 'STD', 'SA' => 'SAR', 'SN' => 'XOF', 'CS' => 'EUR', 'SC' => 'SCR', 'SL' => 'SLL', 'SG' => 'SGD', 'SK' => 'EUR', 'SI' => 'EUR', 'SB' => 'SBD', 'SO' => 'SOS', 'ZA' => 'ZAR', 'GS' => 'GBP', 'ES' => 'EUR', 'LK' => 'LKR',
        'SH' => 'SHP', 'PM' => 'EUR', 'SD' => 'SDG', 'SR' => 'SRG', 'SJ' => 'NOK', 'SZ' => 'SZL', 'SE' => 'SEK', 'CH' => 'CHF', 'SY' => 'SYP', 'TW' => 'TWD', 'TJ' => 'TJR', 'TZ' => 'TZS', 'TH' => 'THB', 'TG' => 'XAF', 'TK' => 'NZD', 'TO' => 'TOP', 'TT' => 'TTD', 'TN' => 'TND', 'TR' => 'TRY', 'TM' => 'TMM', 'TC' => 'USD', 'TV' => 'AUD', 'UG' => 'UGS', 'UA' => 'UAH', 'SU' => 'SUR', 'AE' => 'AED', 'GB' => 'GBP', 'US' => 'USD', 'UM' => 'USD', 'UY' => 'UYU', 'UZ' => 'UZS', 'VU' => 'VUV', 'VE' => 'VEF', 'VN' => 'VND', 'VG' => 'USD', 'VI' => 'USD', 'WF' => 'XPF', 'XO' => 'XOF', 'EH' => 'MAD', 'ZM' => 'ZMK', 'ZW' => 'USD'
    );
    /**
     * Carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $_carrierHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     *
     * @var \Magento\Directory\Model\Region
     */
    protected $_regionModel;

    /**
     *
     * @var \CollinsHarper\CanadaPost\Model\ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;


    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;



    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkout_session;




    /**
     * @param Context $context
     * @param \CollinsHarper\Core\Logger\Logger $chLogged
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param StockRegistryInterface $stockRegistry
     * @param \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory
     * @param \Magento\Directotypery\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Shipping\Helper\Carrier $carrierHelper
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Directory\Model\Region $region
     * 
     */
    public function __construct(
        Context $context,
        \CollinsHarper\Core\Logger\Logger $chLogged,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        StockRegistryInterface $stockRegistry,
        \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Shipping\Helper\Carrier $carrierHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Directory\Model\Region $region
    )
    {
        $this->_moduleManager = $context->getModuleManager();
        $this->_logger = $context->getLogger();
        $this->_chLogged = $chLogged;
        $this->_regionFactory = $regionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->_carrierHelper = $carrierHelper;
        $this->_resourceConfig = $resourceConfig;
        $this->productRepository = $productRepository;
        $this->_checkout_session = $checkoutSession;
        $this->_regionModel = $region;
        $this->objectFactory = $objectFactory;
        $this->_cache = $cache;
        $this->_currencyFactory = $currencyFactory;
        $this->_request = $context->getRequest();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_httpHeader = $context->getHttpHeader();
        $this->_eventManager = $context->getEventManager();
        $this->_remoteAddress = $context->getRemoteAddress();
        $this->_cacheConfig = $context->getCacheConfig();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->urlDecoder = $context->getUrlDecoder();
        $this->scopeConfig = $context->getScopeConfig();
    }

    public function isMock()
    {
        return $this->is_mock == true;
    }

    public function setMockData($data)
    {
        $this->mock_config = $data;
        $this->is_mock = true;
    }

    public function getMockData()
    {
        return $this->mock_config;
    }

    public function setShipmentLinkModel($model)
    {
        $this->shipmentLink = $model;
    }

    public function getShipmentLinkModel()
    {
        if(!$this->shipmentLink) {
            $this->shipmentLink = $this->objectFactory->setClass('CollinsHarper\CanadaPost\Model\Link');
        }
        return $this->shipmentLink;
    }
    /**
     * 
     * @param string $path
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeType
     * @param string $scopeCode
     * @return string
     */
    public function getConfigValueForXml($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->getConfigValue($path, $scopeType, $scopeCode) ? 'true' : 'false';
    }

    /**
     * 
     * @param string $path
     * @param string $scopeType
     * @param string $scopeCode
     * @return mixed
     */
    public function getConfigValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $value = $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
        if($this->isMock()) {
            $value = isset($this->mock_config[$path]) ? $this->mock_config[$path]: $value;
        }
        return $value;
    }


    /**
     * 
     * @return string
     */
    public function _getTokenData()
    {

        if ($this->isTestMode()) {

            $auth =  $this->getConfigValue(self::XML_PATH_TEST_API_LOGIN) . ':' . $this->getConfigValue(self::XML_PATH_TEST_API_PASSWORD);

        } else {

            $auth =  $this->getConfigValue(self::XML_PATH_API_LOGIN) . ':' . $this->getConfigValue(self::XML_PATH_API_PASSWORD);

        }

        $this->_chLogged->info(__METHOD__ . "canada post service auth: " . $auth);

        return $auth;

    }

    /**
     * 
     * @param string $path
     * @param mixed $value
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function saveConfigData($path, $value, $scope = 'default', $scopeId = 0)
    {
        $this->_resourceConfig->saveConfig(
            $path,
            $value,
            $scope,
            $scopeId
        );
        return $this;
    }

    /**
     * 
     * @param boolean $adminReturnUrl
     * @param boolean $registrationToken
     * @return array
     */
    public function getAdminSignUpFormData($adminReturnUrl = false, $registrationToken = false)
    {
        $postData = array(
            'first-name' => '',
            'last-name' => '',
            'address-line-1' => $this->getStoreStreetOne(),
            'prov-state' => $this->getStoreRegionCode(),
            'postal-zip-code' => $this->getStorePostcode(),
            'country-code' => $this->getStoreCountry(),
            'email' => '',
            'city' => $this->getStoreCity(),
            'commercial' => true,
            //            'hasSavedCards' => true,
            //           'hasMultipleCards' => true,
            //          'hasMultipleAccounts' => true,
            //         'hasMultipleContracts' => true,
        );

        //$postData['token-id'] = (string) $this->getAdminSignUpRegistrationToken();
        $postData['token-id'] = (string) $registrationToken;

        if (!empty($postData['token-id'])) {

            $postData['return-url'] = $adminReturnUrl;

            $postData['platform-id'] = $this->getConfigValue(self::XML_PATH_PLATFORM_ID);


            $postData['language'] = $this->getLocale();

        } else {

            $postData = array();

        }
        return $postData ;
    }

    /**
     * 
     * @return type
     */
    public function getAdminSignUpRegistrationToken()
    {
        return $this->objectFactory->setClass('CollinsHarper\CanadaPost\Helper\Rest\Registration')->create()->getRegistrationToken();
    }

    /**
     * 
     * @return mixed
     */
    public function getAdminSignUpFormUrl()
    {
        return $this->getConfigValue(self::XML_PATH_SIGNUP_URL);

    }

    public function getDefaultLength()
    {
        return $this->getDefaultDim();
    }

    public function getDefaultWidth()
    {
        return $this->getDefaultDim(Measure::XML_PATH_DEFAULT_WIDTH);
    }

    public function getDefaultHeight()
    {
        return $this->getDefaultDim(Measure::XML_PATH_DEFAULT_HEIGHT);
    }

    public function getDefaultDim($which = Measure::XML_PATH_DEFAULT_LENGTH)
    {
        return round($this->_carrierHelper->convertMeasureDimension(
            (float)$this->getConfigValue($which),
            $this->getConfigValue(Measure::XML_PATH_DEFAULT_MEASURE_UNIT),
            \Zend_Measure_Length::CENTIMETER
        ), 3);
    }
    
    /**
     * 
     * @param mixed $value
     * @param boolean $from
     * @param string $to
     * @return float
     */
    public function getConvertedDimension($value, $from = false, $to = \Zend_Measure_Length::CENTIMETER)
    {
        if(!$from) {
            $from = $this->getConfigValue(Measure::XML_PATH_DEFAULT_MEASURE_UNIT);
        }

        return round($this->_carrierHelper->convertMeasureDimension(
            (float)$value,
            $from,
            $to
        ), 3);
    }

    /**
     * 
     * @param mixed $value
     * @param boolean $from
     * @param string $to
     * @return float
     */
    public function getConvertedWeight($value, $from = false, $to = \Zend_Measure_Weight::KILOGRAM)
    {
        if(!$from) {
            $from = $this->getConfigValue(Measure::XML_PATH_DEFAULT_WEIGHT_UNIT);
        }

        return round($this->_carrierHelper->convertMeasureWeight(
            (float)$value,
            $from,
            $to
        ), 3);
    }

    // TODO identify the datatype of return
    /**
     * 
     * @param string $code
     * @return type
     */
    protected function _getBaseCurrencyRate($code)
    {
        if (!$this->_baseCurrencyRate) {
            $this->_baseCurrencyRate = $this->_currencyFactory->create()->load(
                $code
            )->getAnyRate(
                $this->_request->getBaseCurrency()->getCode()
            );
        }

        return $this->_baseCurrencyRate;
    }


    /**
     * 
     * @return mixed
     */
    public function getRequireSignature()
    {
        return $this->getConfigValue(self::XML_PATH_SIGNATURE);
    }

    /**
     * 
     * @return mixed
     */
    public function getRequireSignatureThreshold()
    {
        return $this->getConfigValue(self::XML_PATH_SIGNATURE_THRESHOLD);
    }

    /**
     * 
     * @return mixed
     */
    public function getLabelOutput()
    {
        return $this->getConfigValue(self::XML_PATH_LABEL_FORMAT);
    }

    /**
     * 
     * @return mixed
     */
    public function getApiPaymentMethod()
    {
        return ($this->getConfigValue(self::XML_PATH_HAS_DEFAULT_CC) ? self::CP_BILLING_CC : self::CP_BILLING_ACCOUNT);
    }

    /**
     * 
     * @return mixed
     */
    public function getExportReason()
    {
        return $this->getConfigValue(self::XML_PATH_NOTIFY_REASON);
    }

    /**
     * 
     * @return mixed
     */
    public function getExportReasonOther()
    {
        return $this->getConfigValue(self::XML_PATH_NOTIFY_OTHER_REASON);
    }

    /**
     * 
     * @return mixed
     */
    public function getApiCustomerNumber()
    {
        return $this->getConfigValue(self::XML_PATH_CUSTOMER_NUMBER);
    }

    /**
     * 
     * @return mixed
     */
    public function getContractId()
    {
        return $this->getConfigValue(self::XML_PATH_CONTRACT_ID);
    }

    /**
     * 
     * @return string
     */
    public function getStorePostcode()
    {
        return  str_replace(' ', '', strtoupper( $this->formatPostalCode($this->getConfigValue(Config::XML_PATH_ORIGIN_POSTCODE))));
    }

    // TODO indentify the datatype of the return
    /**
     * 
     * @return type
     */
    public function getStoreRegionCode()
    {
        return $this->_regionModel->unsetData()->load($this->getConfigValue(Config::XML_PATH_ORIGIN_REGION_ID))->getCode();
    }

    /**
     * 
     * @return mixed
     */
    public function getStoreCity()
    {
        return $this->getConfigValue(Config::XML_PATH_ORIGIN_CITY);
    }

    /**
     * 
     * @return mixed
     */
    public function getStoreCountry()
    {
        return $this->getConfigValue(Config::XML_PATH_ORIGIN_COUNTRY_ID);
    }

    /**
     * 
     * @return mixed
     */
    public function getStoreStreetOne()
    {
        return $this->getConfigValue(Shipment::XML_PATH_STORE_ADDRESS1);
    }

    /**
     * 
     * @return mixed
     */
    public function getStoreCompany()
    {
        return $this->getConfigValue(Information::XML_PATH_STORE_INFO_NAME);
    }

    /**
     * 
     * @return mixed
     */
    public function getStorePhone()
    {
        return $this->getConfigValue(Information::XML_PATH_STORE_INFO_PHONE);
    }

    /**
     * 
     * @param string $country
     * @return string
     */
    public function _getDefaultCurrencyFromCountry($country)
    {

        if(isset($this->_currencies_map[$country]))
        {
            return $this->_currencies_map[$country];
        }
        return self::CURRENCY_USD;
    }

    /**
     * 
     * @return string|mixed
     */
    public function getBaseUrl()
    {

        if ($this->isTestMode()) {

            $url = $this->getConfigValue(self::XML_PATH_TEST_URL);

        } else {

            $url = $this->getConfigValue(self::XML_PATH_URL);

        }

        if (!preg_match('/\/$/', $url)) {

            $url .= '/';

        }

        return $url;

    }

    /**
     * 
     * @return string
     */
    public function getError()
    {

        return $this->error;

    }


    /**
     * 
     * @param string $code
     * @return string
     */
    public function formatPostalCode($code) {

        return strtoupper(str_replace(' ', '', $code));

    }

    /**
     * 
     * @return boolean
     */
    public function isContract() {

        $contract_number = $this->getConfigValue(self::XML_PATH_CONTRACT_ID);

        return (!empty($contract_number));

    }

    /**
     * 
     * @return string
     */
    public function getBehalfAccount()
    {

        $behalf_customer = $this->getConfigValue(self::XML_PATH_CUSTOMER_NUMBER);

        if (!$this->isTestMode()) {

            $behalf_customer .= '-' . $this->getConfigValue(self::XML_PATH_PLATFORM_ID);

        }

        return $behalf_customer;

    }

    /**
     * 
     * @return boolean
     */
    public function isTestMode()
    {
        return !$this->force_live && $this->getConfigValue(self::XML_PATH_TEST_MODE);
    }

    /**
     * 
     * @return $this
     */
    public function forceLive()
    {

        $this->force_live = true;

        return $this;

    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        if (null === $this->_locale) {
            $this->_locale = $this->objectFactory->setClass('Magento\Framework\Locale\ResolverInterface')->create();
        }
        return $this->_locale->getLocale();
    }

    /**
     * 
     * @param int $returnLanguage
     * @return String
     */
    public function getLocale($returnLanguage = 2) {

        $return = self::EN_TEST;

        if ($this->getConfigValue(self::XML_PATH_LOCALE)) {

            // TODO switch to getCurrentLocale()
            if ($this->getConfigValue(\Magento\Store\Model\ScopeInterface::XML_PATH_DEFAULT_LOCALE) == self::LANG_MAGE_FR_CA) {

                $return = self::LANG_CP_RATE_FR;

            }

        }

        if ($returnLanguage != 2) {

            if ($return == self::FR_TEST) {

                $return = self::LANG_CP_RATE_FR;

            }

            if ($return == self::EN_TEST) {

                $return = self::LANG_CP_RATE_EN;

            }

        }

        return $return;

    }

    /**
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $productId
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getProduct($item = null, $productId = null)
    {
        $product = null;
        if($item) {
            $productId = $item->getProduct() ? $item->getProduct()->getId() : $item->getProductId();
        }

        if($productId) {
            $product = $this->productRepository->getById($productId);
        }

        return $product && $product->getId() ? $product : null;
    }

    /**
     * 
     * @param int $productId
     * @return bool
     */
    public function isProductIdInStock($productId)
    {
        $stockItem = $this->getStockItemByProductId($productId);
        return $stockItem && $stockItem->getIsInStock();
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getStockItemByProduct($product)
    {
        return $this->getStockItemByProductId($product->getId());
    }

    /**
     * 
     * @param int $productId
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getStockItemByProductId($productId)
    {

        try {
            // TODO how do we manage scope id?
            // $product->getStore()->getWebsiteId()
            $stockItem = $this->stockRegistry->getStockItem(
                $productId
            );
            return $stockItem;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
           // return false;
            return null;
        }
    }


}
