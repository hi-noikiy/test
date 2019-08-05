<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CollinsHarper\CanadaPost\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Module\Dir;
use Magento\Sales\Model\Order\Shipment as MageShipment;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;;
use \Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;
use CollinsHarper\CanadaPost\Helper\Data as CPHelper;
use \CollinsHarper\CanadaPost\Helper\Option as CPOption;


/**
 * Canada Post Sell Online (* deprecated)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    /**#@+
     * Carrier Product indicator
     */
    const PACKAGE_TYPE = 'N';
    /**#@-*/

    /**
     * Code of the carrier
     */
    const CODE = 'cpcanadapost';

    const DEFAULT_DIM = 5;
    const DEFAULT_WEIGHT = .7;
    const ENGLISH_LANG = 'EN';
    const FRENCH_LANG = 'FR';
    const MAX_ITEM_TITLE_LENGTH = 32;
    const DEMO_ID = ' CPC_DEMO_XML ';
    const WEIGHT_FAILURE_REASON = 'weight';
    const WEIGHT_FAILURE_TEST = 0.01;


    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::CODE;


    /*
     *  @var CollinsHarper\CanadaPost\Model\Source\Method\List
     */
    protected $_service_map = false;



    /**
     * Container types that could be customized
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = [self::PACKAGE_TYPE];


    /**
     * Rate request data
     *
     * @var RateRequest|null
     */
    protected $_request;

    /**
     * Rate result data
     *
     * @var Result|null
     */
    protected $_result;

    /**
     * Countries parameters data
     *
     * @var \Magento\Shipping\Model\Simplexml\Element|null
     */
    protected $_countryParams;

    /**
     * Errors placeholder
     *
     * @var string[]
     */
    protected $_errors = [];

    /**
     * CP rates result
     *
     * @var array
     */
    protected $_rates = [];

    /**
     * Max weight without fee
     *
     * @var int
     */
    protected $_maxWeight = 70;

    /**
     * Flag if response is for shipping label creating
     *
     * @var bool
     */
    protected $_isShippingLabelFlag = false;


    /**
     * Flag that shows if shipping is domestic
     *
     * @var bool
     */
    protected $_isDomestic = true;

    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * Carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $_carrierHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_configReader;

    /**
     * @var \Magento\Framework\Math\Division
     */
    protected $mathDivision;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \CollinsHarper\CanadaPost\Model\ObjectFactory
     */
    protected $ObjectFactory;

    /**
     * @var \CollinsHarper\CanadaPost\Helper\Data
     */
    protected $cpHelper;


    /**
     * @var \CollinsHarper\CanadaPost\Helper\Rest\GetRates
     */
    protected $rateApi;


    /**
     * @var \CollinsHarper\CanadaPost\Helper\Option
     */
    protected $optionHelper;


    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Shipping\Helper\Carrier $carrierHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\Module\Dir\Reader $configReader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Division $mathDivision
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \CollinsHarper\CanadaPost\Model\ObjectFactory $ObjectFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Shipping\Helper\Carrier $carrierHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \CollinsHarper\CanadaPost\Model\ObjectFactory $ObjectFactory,
        array $data = []
    ) {
        $this->readFactory = $readFactory;
        $this->_carrierHelper = $carrierHelper;
        $this->_coreDate = $coreDate;
        $this->_storeManager = $storeManager;
        $this->_configReader = $configReader;
        $this->string = $string;
        $this->mathDivision = $mathDivision;
        $this->_dateTime = $dateTime;
        $this->_httpClientFactory = $httpClientFactory;
        $this->objectFactory = $ObjectFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Returns value of given variable
     *
     * @param string|int $origValue
     * @param string $pathToValue
     * @return string|int|null
     */
    protected function _getDefaultValue($origValue, $pathToValue)
    {
        if (!$origValue) {
            $origValue = $this->_scopeConfig->getValue(
                $pathToValue,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            );
        }

        return $origValue;
    }

    /**
     * Set Free Method Request
     *
     * @param string $freeMethod
     * @return void
     */
    protected function _setFreeMethodRequest($freeMethod)
    {
        $this->_rawRequest->setFreeMethodRequest(true);
        $freeWeight = $this->getTotalNumOfBoxes($this->_rawRequest->getFreeMethodWeight());
        $this->_rawRequest->setWeight($freeWeight);
        $this->_rawRequest->setService($freeMethod);
    }

    /**
     * Returns request result
     *
     * @return Result|null
     */
    public function getResult()
    {
        return $this->_result;
    }


    /**
     * Returns ch Helper
     *
     * @return Result|null
     */
    public function getOptionHelper()
    {
        if(!$this->optionHelper) {
            $this->optionHelper = $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Helper\Option');
        }
        return $this->optionHelper;
    }

    /**
     * Returns ch Helper
     *
     * @return Result|null
     */
    public function getRateApi()
    {
        if(!$this->rateApi) {
            $this->rateApi = $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Helper\Rest\GetRates');
        }
        return $this->rateApi;
    }
    /**
     * Returns ch Helper
     *
     * @return Result|null
     */
    public function getCpHelper()
    {
        if(!$this->cpHelper) {
            $this->cpHelper = $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Helper\Data');
        }
        return $this->cpHelper;
    }

    /**
     * Returns ch Helper
     *
     * @return Result|null
     */
    public function getConfigValue($path, $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->cpHelper->getConfigValue($path, $scopeType, $scopeCode);
    }


    /**
     * Prepare and set request in property of current instance
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setRequest(\Magento\Framework\DataObject $request)
    {
        $this->_request = $request;
        $this->setStore($request->getStoreId());

        $requestObject = new \Magento\Framework\DataObject();

        $requestObject->setIsGenerateLabelReturn($request->getIsGenerateLabelReturn());

        $requestObject->setStoreId($request->getStoreId());

        if ($request->getLimitMethod()) {
            $requestObject->setService($request->getLimitMethod());
        }

        $requestObject = $this->_addParams($requestObject);

        if ($request->getDestPostcode()) {
            $requestObject->setDestPostal($request->getDestPostcode());
        }

        $requestObject->setOrigCountry(
            $this->_getDefaultValue($request->getOrigCountry(), MageShipment::XML_PATH_STORE_COUNTRY_ID)
        )->setOrigCountryId(
            $this->_getDefaultValue($request->getOrigCountryId(), MageShipment::XML_PATH_STORE_COUNTRY_ID)
        );

        $shippingWeight = $request->getPackageWeight();

        $requestObject->setValue(round($request->getPackageValue(), 2))
            ->setValueWithDiscount($request->getPackageValueWithDiscount())
            ->setCustomsValue($request->getPackageCustomsValue())
            ->setDestStreet($this->string->substr(str_replace("\n", '', $request->getDestStreet()), 0, 35))
            ->setDestStreetLine2($request->getDestStreetLine2())
            ->setDestCity($request->getDestCity())
            ->setOrigCompanyName($request->getOrigCompanyName())
            ->setOrigCity($request->getOrigCity())
            ->setOrigPhoneNumber($request->getOrigPhoneNumber())
            ->setOrigPersonName($request->getOrigPersonName())
            ->setOrigEmail(
                $this->_scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $requestObject->getStoreId()
                )
            )
            ->setOrigCity($request->getOrigCity())
            ->setOrigPostal($request->getOrigPostal())
            ->setOrigStreetLine2($request->getOrigStreetLine2())
            ->setDestPhoneNumber($request->getDestPhoneNumber())
            ->setDestPersonName($request->getDestPersonName())
            ->setDestCompanyName($request->getDestCompanyName());

        $originStreet2 = $this->_scopeConfig->getValue(
            MageShipment::XML_PATH_STORE_ADDRESS2,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $requestObject->getStoreId()
        );

        $requestObject->setOrigStreet($request->getOrigStreet() ? $request->getOrigStreet() : $originStreet2);

        if (is_numeric($request->getOrigState())) {
            $requestObject->setOrigState($this->_regionFactory->create()->load($request->getOrigState())->getCode());
        } else {
            $requestObject->setOrigState($request->getOrigState());
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }


        $requestObject->setDestCountryId($destCountry)
            ->setDestState($request->getDestRegionCode())
            ->setWeight($shippingWeight)
            ->setFreeMethodWeight($request->getFreeMethodWeight())
            ->setOrderShipment($request->getOrderShipment());

        if ($request->getPackageId()) {
            $requestObject->setPackageId($request->getPackageId());
        }

        $requestObject->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->setRawRequest($requestObject);

        return $this;
    }

    /**
     * Get allowed shipping methods
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllowedMethods()
    {
        return explode(',', $this->getConfigData('methods'));
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'unit_of_measure' => ['L' => __('Pounds'), 'K' => __('Kilograms')],
            'unit_of_dimension' => ['I' => __('Inches'), 'C' => __('Centimeters')],
            'unit_of_dimension_cut' => ['I' => __('inch'), 'C' => __('cm')],
            'dimensions' => ['HEIGHT' => __('Height'), 'DEPTH' => __('Depth'), 'WIDTH' => __('Width')],
            'size' => ['0' => __('Regular'), '1' => __('Specific')],
            'dimensions_variables' => [
                'L' => \Zend_Measure_Weight::POUND,
                'LB' => \Zend_Measure_Weight::POUND,
                'POUND' => \Zend_Measure_Weight::POUND,
                'K' => \Zend_Measure_Weight::KILOGRAM,
                'KG' => \Zend_Measure_Weight::KILOGRAM,
                'KILOGRAM' => \Zend_Measure_Weight::KILOGRAM,
                'I' => \Zend_Measure_Length::INCH,
                'IN' => \Zend_Measure_Length::INCH,
                'INCH' => \Zend_Measure_Length::INCH,
                'C' => \Zend_Measure_Length::CENTIMETER,
                'CM' => \Zend_Measure_Length::CENTIMETER,
                'CENTIMETER' => \Zend_Measure_Length::CENTIMETER,
            ],
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        $code = strtoupper($code);
        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Convert item weight to needed weight based on config weight unit dimensions
     *
     * @param float $weight
     * @param bool $maxWeight
     * @param string|bool $configWeightUnit
     * @return float
     */
    protected function _getWeight($weight, $maxWeight = false, $configWeightUnit = false)
    {
        // TODO do we use ours or just use theirs?
        if ($maxWeight) {
            $configWeightUnit = \Zend_Measure_Weight::KILOGRAM;
        } elseif ($configWeightUnit) {
            $configWeightUnit = $this->getCode('dimensions_variables', $configWeightUnit);
        } else {
            $configWeightUnit = $this->getCode(
                'dimensions_variables',
                (string)$this->getConfigData('unit_of_measure')
            );
        }
    $configWeightUnit = \Zend_Measure_Weight::POUND;
        $countryWeightUnit = \Zend_Measure_Weight::KILOGRAM; // $this->getCode('dimensions_variables', $this->_getWeightUnit());

        if ($configWeightUnit != $countryWeightUnit) {
            $weight = $this->_carrierHelper->convertMeasureWeight(
                round($weight, 3),
                $configWeightUnit,
                $countryWeightUnit
            );
        }

        return round($weight, 3);
    }

    /**
     * Prepare items to pieces
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    // TODO This is not going tohelp us with RTS or boxing
    protected function _getAllItems()
    {
        $allItems = $this->_request->getAllItems();
        $fullItems = [];

        foreach ($allItems as $item) {
            if ($item->getProductType() == Type::TYPE_BUNDLE && $item->getProduct()->getShipmentType()) {
                continue;
            }

            $qty = $item->getQty();
            $changeQty = true;
            $checkWeight = true;
            $decimalItems = [];

            if ($item->getParentItem()) {
                if (!$item->getParentItem()->getProduct()->getShipmentType()) {
                    continue;
                }
                if ($item->getIsQtyDecimal()) {
                    $qty = $item->getParentItem()->getQty();
                } else {
                    $qty = $item->getParentItem()->getQty() * $item->getQty();
                }
            }

            $itemWeight = $item->getWeight();
            if ($item->getIsQtyDecimal() && $item->getProductType() != Type::TYPE_BUNDLE) {
                $productId = $item->getProduct()->getId();
                $stockItemDo = $this->stockRegistry->getStockItem($productId, $item->getStore()->getWebsiteId());
                $isDecimalDivided = $stockItemDo->getIsDecimalDivided();
                if ($isDecimalDivided) {
                    if ($stockItemDo->getEnableQtyIncrements()
                        && $stockItemDo->getQtyIncrements()
                    ) {
                        $itemWeight = $itemWeight * $stockItemDo->getQtyIncrements();
                        $qty = round($item->getWeight() / $itemWeight * $qty);
                        $changeQty = false;
                    } else {
                        $itemWeight = $this->_getWeight($itemWeight * $item->getQty());
                        $maxWeight = $this->_getWeight($this->_maxWeight, true);
                        if ($itemWeight > $maxWeight) {
                            $qtyItem = floor($itemWeight / $maxWeight);
                            $decimalItems[] = ['weight' => $maxWeight, 'qty' => $qtyItem];
                            $weightItem = $this->mathDivision->getExactDivision($itemWeight, $maxWeight);
                            if ($weightItem) {
                                $decimalItems[] = ['weight' => $weightItem, 'qty' => 1];
                            }
                            $checkWeight = false;
                        }
                    }
                } else {
                    $itemWeight = $itemWeight * $item->getQty();
                }
            }

            if ($checkWeight && $this->_getWeight($itemWeight) > $this->_getWeight($this->_maxWeight, true)) {
                return [];
            }

            if ($changeQty
                && !$item->getParentItem()
                && $item->getIsQtyDecimal()
                && $item->getProductType() != Type::TYPE_BUNDLE
            ) {
                $qty = 1;
            }

            if (!empty($decimalItems)) {
                foreach ($decimalItems as $decimalItem) {
                    $fullItems = array_merge(
                        $fullItems,
                        array_fill(0, $decimalItem['qty'] * $qty, $decimalItem['weight'])
                    );
                }
            } else {
                $fullItems = array_merge($fullItems, array_fill(0, $qty, $this->_getWeight($itemWeight)));
            }
        }
        sort($fullItems);

        return $fullItems;
    }

    /**
     * Make pieces
     *
     * @param \Magento\Shipping\Model\Simplexml\Element $nodeBkgDetails
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    // TODO This is not going tohelp us with RTS or boxing

    protected function _makePieces(\Magento\Shipping\Model\Simplexml\Element $nodeBkgDetails)
    {
        $divideOrderWeight = (string)$this->getConfigData('divide_order_weight');
        $nodePieces = $nodeBkgDetails->addChild('Pieces', '', '');
        $items = $this->_getAllItems();
        $numberOfPieces = 0;

        if ($divideOrderWeight && !empty($items)) {
            $maxWeight = $this->_getWeight($this->_maxWeight, true);
            $sumWeight = 0;

            $reverseOrderItems = $items;
            arsort($reverseOrderItems);

            foreach ($reverseOrderItems as $key => $weight) {
                if (!isset($items[$key])) {
                    continue;
                }
                unset($items[$key]);
                $sumWeight = $weight;
                foreach ($items as $key => $weight) {
                    if ($sumWeight + $weight < $maxWeight) {
                        unset($items[$key]);
                        $sumWeight += $weight;
                    } elseif ($sumWeight + $weight > $maxWeight) {
                        $numberOfPieces++;
                        $nodePiece = $nodePieces->addChild('Piece', '', '');
                        $nodePiece->addChild('PieceID', $numberOfPieces);
                        $this->_addDimension($nodePiece);
                        $nodePiece->addChild('Weight', $sumWeight);
                        break;
                    } else {
                        unset($items[$key]);
                        $numberOfPieces++;
                        $sumWeight += $weight;
                        $nodePiece = $nodePieces->addChild('Piece', '', '');
                        $nodePiece->addChild('PieceID', $numberOfPieces);
                        $this->_addDimension($nodePiece);
                        $nodePiece->addChild('Weight', $sumWeight);
                        $sumWeight = 0;
                        break;
                    }
                }
            }
            if ($sumWeight > 0) {
                $numberOfPieces++;
                $nodePiece = $nodePieces->addChild('Piece', '', '');
                $nodePiece->addChild('PieceID', $numberOfPieces);
                $this->_addDimension($nodePiece);
                $nodePiece->addChild('Weight', $sumWeight);
            }
        } else {
            $nodePiece = $nodePieces->addChild('Piece', '', '');
            $nodePiece->addChild('PieceID', 1);
            $this->_addDimension($nodePiece);
            $nodePiece->addChild('Weight', $this->_getWeight($this->_rawRequest->getWeight()));
        }

        $handlingAction = $this->getConfigData('handling_action');
        if ($handlingAction == AbstractCarrier::HANDLING_ACTION_PERORDER || !$numberOfPieces) {
            $numberOfPieces = 1;
        }
        $this->_numBoxes = $numberOfPieces;
    }

    /**
     * Convert item dimension to needed dimension based on config dimension unit of measure
     *
     * @param float $dimension
     * @param string|bool $configWeightUnit
     * @return float
     */
    protected function _getDimension($dimension, $configWeightUnit = false)
    {
        if (!$configWeightUnit) {
            $configWeightUnit = $this->getCode(
                'dimensions_variables',
                (string)$this->getConfigData('unit_of_measure')
            );
        } else {
            $configWeightUnit = $this->getCode('dimensions_variables', $configWeightUnit);
        }

        if ($configWeightUnit == \Zend_Measure_Weight::POUND) {
            $configDimensionUnit = \Zend_Measure_Length::INCH;
        } else {
            $configDimensionUnit = \Zend_Measure_Length::CENTIMETER;
        }

        if ($configDimensionUnit != \Zend_Measure_Length::CENTIMETER) {
            $dimension = $this->_carrierHelper->convertMeasureDimension(
                round($dimension, 3),
                $configDimensionUnit,
                \Zend_Measure_Length::CENTIMETER
            );
        }

        return round($dimension, 3);
    }

    /**
     * Add dimension to piece
     *
     * @param \Magento\Shipping\Model\Simplexml\Element $nodePiece
     * @return void
     */
    // TODO deprecate4
    protected function _addDimension($nodePiece)
    {
        $sizeChecker = (string)$this->getConfigData('size');

        $height = $this->_getDimension((string)$this->getConfigData('height'));
        $depth = $this->_getDimension((string)$this->getConfigData('depth'));
        $width = $this->_getDimension((string)$this->getConfigData('width'));

        if ($sizeChecker && $height && $depth && $width) {
            $nodePiece->addChild('Height', $height);
            $nodePiece->addChild('Depth', $depth);
            $nodePiece->addChild('Width', $width);
        }
    }

    /**
     * Get shipping quotes
     *
     * @return \Magento\Framework\Model\AbstractModel|Result
     */
    protected function _getQuotes()
    {
        $responseBody = '';
        try {
            $debugData = [];
            for ($offset = 0; $offset <= self::UNAVAILABLE_DATE_LOOK_FORWARD; $offset++) {
                $debugData['try-' . $offset] = [];
                $debugPoint = &$debugData['try-' . $offset];

                $requestXml = $this->_buildQuotesRequestXml();
                $date = date(self::REQUEST_DATE_FORMAT, strtotime($this->_getShipDate() . " +{$offset} days"));
                $this->_setQuotesRequestXmlDate($requestXml, $date);

                $request = $requestXml->asXML();
                $debugPoint['request'] = $request;
                $responseBody = $this->_getCachedQuotes($request);
                $debugPoint['from_cache'] = $responseBody === null;

                if ($debugPoint['from_cache']) {
                    $responseBody = $this->_getQuotesFromServer($request);
                }

                $debugPoint['response'] = $responseBody;

                $bodyXml = $this->_xmlElFactory->create(['data' => $responseBody]);
                $code = $bodyXml->xpath('//GetQuoteResponse/Note/Condition/ConditionCode');
                if (isset($code[0]) && (int)$code[0] == self::CONDITION_CODE_SERVICE_DATE_UNAVAILABLE) {
                    $debugPoint['info'] = sprintf(__("DHL service is not available at %s date"), $date);
                } else {
                    break;
                }

                $this->_setCachedQuotes($request, $responseBody);
            }
            $this->_debug($debugData);
        } catch (\Exception $e) {
            $this->_errors[$e->getCode()] = $e->getMessage();
        }

        return $this->_parseResponse($responseBody);
    }

    /**
     * Get shipping quotes from DHL service
     *
     * @param string $request
     * @return string
     */
    protected function _getQuotesFromServer($request)
    {
        $client = $this->_httpClientFactory->create();
        $client->setUri((string)$this->getConfigData('gateway_url'));
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setRawData(utf8_encode($request));

        return $client->request(\Zend_Http_Client::POST)->getBody();
    }

    /**
     * Build quotes request XML object
     *
     * @return \SimpleXMLElement
     */
    protected function _buildQuotesRequestXml()
    {
        $rawRequest = $this->_rawRequest;
        $xmlStr = '<?xml version = "1.0" encoding = "UTF-8"?>' .
            '<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" ' .
            'xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" ' .
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
            'xsi:schemaLocation="http://www.dhl.com DCT-req.xsd "/>';
        $xml = $this->_xmlElFactory->create(['data' => $xmlStr]);
        $nodeGetQuote = $xml->addChild('GetQuote', '', '');
        $nodeRequest = $nodeGetQuote->addChild('Request');

        $nodeServiceHeader = $nodeRequest->addChild('ServiceHeader');
        $nodeServiceHeader->addChild('SiteID', (string)$this->getConfigData('id'));
        $nodeServiceHeader->addChild('Password', (string)$this->getConfigData('password'));

        $nodeFrom = $nodeGetQuote->addChild('From');
        $nodeFrom->addChild('CountryCode', $rawRequest->getOrigCountryId());
        $nodeFrom->addChild('Postalcode', $rawRequest->getOrigPostal());
        $nodeFrom->addChild('City', $rawRequest->getOrigCity());

        $nodeBkgDetails = $nodeGetQuote->addChild('BkgDetails');
        $nodeBkgDetails->addChild('PaymentCountryCode', $rawRequest->getOrigCountryId());
        $nodeBkgDetails->addChild(
            'Date',
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        $nodeBkgDetails->addChild('ReadyTime', 'PT' . (int)(string)$this->getConfigData('ready_time') . 'H00M');

        $nodeBkgDetails->addChild('DimensionUnit', $this->_getDimensionUnit());
        $nodeBkgDetails->addChild('WeightUnit', $this->_getWeightUnit());

        $this->_makePieces($nodeBkgDetails);

        $nodeBkgDetails->addChild('PaymentAccountNumber', (string)$this->getConfigData('account'));

        $nodeTo = $nodeGetQuote->addChild('To');
        $nodeTo->addChild('CountryCode', $rawRequest->getDestCountryId());
        $nodeTo->addChild('Postalcode', $rawRequest->getDestPostal());
        $nodeTo->addChild('City', $rawRequest->getDestCity());

        if ($this->isDutiable($rawRequest->getOrigCountryId(), $rawRequest->getDestCountryId())) {
            // IsDutiable flag and Dutiable node indicates that cargo is not a documentation
            $nodeBkgDetails->addChild('IsDutiable', 'Y');
            $nodeDutiable = $nodeGetQuote->addChild('Dutiable');
            $baseCurrencyCode = $this->_storeManager
                ->getWebsite($this->_request->getWebsiteId())
                ->getBaseCurrencyCode();
            $nodeDutiable->addChild('DeclaredCurrency', $baseCurrencyCode);
            $nodeDutiable->addChild('DeclaredValue', sprintf("%.2F", $rawRequest->getValue()));
        }

        return $xml;
    }

    /**
     * Set pick-up date in request XML object
     *
     * @param \SimpleXMLElement $requestXml
     * @param string $date
     * @return \SimpleXMLElement
     */
    protected function _setQuotesRequestXmlDate(\SimpleXMLElement $requestXml, $date)
    {
        $requestXml->GetQuote->BkgDetails->Date = $date;

        return $requestXml;
    }

    /**
     * Parse response from DHL web service
     *
     * @param string $response
     * @return bool|\Magento\Framework\DataObject|Result|Error
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _parseResponse($response)
    {
        $responseError = __('The response is in wrong format.');

        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                $xml = $this->parseXml($response, 'Magento\Shipping\Model\Simplexml\Element');
                if (is_object($xml)) {
                    if (in_array($xml->getName(), ['ErrorResponse', 'ShipmentValidateErrorResponse'])
                        || isset($xml->GetQuoteResponse->Note->Condition)
                    ) {
                        $code = null;
                        $data = null;
                        if (isset($xml->Response->Status->Condition)) {
                            $nodeCondition = $xml->Response->Status->Condition;
                        } else {
                            $nodeCondition = $xml->GetQuoteResponse->Note->Condition;
                        }

                        if ($this->_isShippingLabelFlag) {
                            foreach ($nodeCondition as $condition) {
                                $code = isset($condition->ConditionCode) ? (string)$condition->ConditionCode : 0;
                                $data = isset($condition->ConditionData) ? (string)$condition->ConditionData : '';
                                if (!empty($code) && !empty($data)) {
                                    break;
                                }
                            }
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Error #%1 : %2', trim($code), trim($data))
                            );
                        }

                        $code = isset($nodeCondition->ConditionCode) ? (string)$nodeCondition->ConditionCode : 0;
                        $data = isset($nodeCondition->ConditionData) ? (string)$nodeCondition->ConditionData : '';
                        $this->_errors[$code] = __('Error #%1 : %2', trim($code), trim($data));
                    } else {
                        if (isset($xml->GetQuoteResponse->BkgDetails->QtdShp)) {
                            foreach ($xml->GetQuoteResponse->BkgDetails->QtdShp as $quotedShipment) {
                                $this->_addRate($quotedShipment);
                            }
                        } elseif (isset($xml->AirwayBillNumber)) {
                            return $this->_prepareShippingLabelContent($xml);
                        } else {
                            $this->_errors[] = $responseError;
                        }
                    }
                }
            } else {
                $this->_errors[] = $responseError;
            }
        } else {
            $this->_errors[] = $responseError;
        }

        /* @var $result Result */
        $result = $this->_rateFactory->create();
        if ($this->_rates) {
            foreach ($this->_rates as $rate) {
                $method = $rate['service'];
                $data = $rate['data'];
                /* @var $rate \Magento\Quote\Model\Quote\Address\RateResult\Method */
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier(self::CODE);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($data['term']);
                $rate->setCost($data['price_total']);
                $rate->setPrice($data['price_total']);
                $result->append($rate);
            }
        } else {
            if (!empty($this->_errors)) {
                if ($this->_isShippingLabelFlag) {
                    throw new \Magento\Framework\Exception\LocalizedException($responseError);
                }
                $this->debugErrors($this->_errors);

                return false;
            }
        }

        return $result;
    }

    /**
     * Add rate to DHL rates array
     *
     * @param \SimpleXMLElement $shipmentDetails
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _addRate(\SimpleXMLElement $shipmentDetails)
    {
        if (isset($shipmentDetails->ProductShortName)
            && isset($shipmentDetails->ShippingCharge)
            && isset($shipmentDetails->GlobalProductCode)
            && isset($shipmentDetails->CurrencyCode)
            && array_key_exists((string)$shipmentDetails->GlobalProductCode, $this->getAllowedMethods())
        ) {
            // DHL product code, e.g. '3', 'A', 'Q', etc.
            $dhlProduct = (string)$shipmentDetails->GlobalProductCode;
            $totalEstimate = (float)(string)$shipmentDetails->ShippingCharge;
            $currencyCode = (string)$shipmentDetails->CurrencyCode;
            $baseCurrencyCode = $this->_storeManager->getWebsite($this->_request->getWebsiteId())
                ->getBaseCurrencyCode();
            $dhlProductDescription = $this->getDhlProductTitle($dhlProduct);

            if ($currencyCode != $baseCurrencyCode) {
                /* @var $currency \Magento\Directory\Model\Currency */
                $currency = $this->_currencyFactory->create();
                $rates = $currency->getCurrencyRates($currencyCode, [$baseCurrencyCode]);
                if (!empty($rates) && isset($rates[$baseCurrencyCode])) {
                    // Convert to store display currency using store exchange rate
                    $totalEstimate = $totalEstimate * $rates[$baseCurrencyCode];
                } else {
                    $rates = $currency->getCurrencyRates($baseCurrencyCode, [$currencyCode]);
                    if (!empty($rates) && isset($rates[$currencyCode])) {
                        $totalEstimate = $totalEstimate / $rates[$currencyCode];
                    }
                    if (!isset($rates[$currencyCode]) || !$totalEstimate) {
                        $totalEstimate = false;
                        $this->_errors[] = __(
                            'We had to skip DHL method %1 because we couldn\'t find exchange rate %2 (Base Currency).',
                            $currencyCode,
                            $baseCurrencyCode
                        );
                    }
                }
            }
            if ($totalEstimate) {
                $data = [
                    'term' => $dhlProductDescription,
                    'price_total' => $this->getMethodPrice($totalEstimate, $dhlProduct),
                ];
                if (!empty($this->_rates)) {
                    foreach ($this->_rates as $product) {
                        if ($product['data']['term'] == $data['term']
                            && $product['data']['price_total'] == $data['price_total']
                        ) {
                            return $this;
                        }
                    }
                }
                $this->_rates[] = ['service' => $dhlProduct, 'data' => $data];
            } else {
                $this->_errors[] = __("Zero shipping charge for '%1'", $dhlProductDescription);
            }
        } else {
            $dhlProductDescription = false;
            if (isset($shipmentDetails->GlobalProductCode)) {
                $dhlProductDescription = $this->getDhlProductTitle((string)$shipmentDetails->GlobalProductCode);
            }
            $dhlProductDescription = $dhlProductDescription ? $dhlProductDescription : __("DHL");
            $this->_errors[] = __("Zero shipping charge for '%1'", $dhlProductDescription);
        }

        return $this;
    }

    /**
     * Returns dimension unit (cm or inch)
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getDimensionUnit()
    {
        $countryId = $this->_rawRequest->getOrigCountryId();
        $measureUnit = $this->getCountryParams($countryId)->getMeasureUnit();
        if (empty($measureUnit)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Cannot identify measure unit for %1", $countryId)
            );
        }

        return $measureUnit;
    }

    /**
     * Returns weight unit (kg or pound)
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getWeightUnit()
    {
        $countryId = $this->_rawRequest->getOrigCountryId();
        $weightUnit = \Zend_Measure_Weight::KILOGRAM; 
        if (empty($weightUnit)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Cannot identify weight unit for %1", $countryId)
            );
        }

        return $weightUnit;
    }

    /**
     * Get Country Params by Country Code
     *
     * @param string $countryCode
     * @return \Magento\Framework\DataObject
     *
     * @see $countryCode ISO 3166 Codes (Countries) A2
     */
    protected function getCountryParams($countryCode)
    {
        if (empty($this->_countryParams)) {
            $etcPath = $this->_configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Dhl');
            $directoryRead = $this->readFactory->create($etcPath);
            $countriesXml = $directoryRead->readFile('countries.xml');
            $this->_countryParams = $this->_xmlElFactory->create(['data' => $countriesXml]);
        }
        if (isset($this->_countryParams->{$countryCode})) {
            $countryParams = new \Magento\Framework\DataObject($this->_countryParams->{$countryCode}->asArray());
        }
        return isset($countryParams) ? $countryParams : new \Magento\Framework\DataObject();
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $this->_mapRequestToShipment($request);
        $this->setRequest($request);

        return $this->_doRequest();
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|\Magento\Framework\DataObject|boolean
     */
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        //Skip by item validation if there is no items in request
        if (!count($this->getAllItems($request))) {
            $this->_errors[] = __('There is no items in this order');
        }

        $countryParams = $this->getCountryParams(
            $this->_scopeConfig->getValue(
                MageShipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            )
        );
        if (!$countryParams->getData()) {
            $this->_errors[] = __('Please, specify origin country');
        }

        if (!empty($this->_errors)) {
            $this->debugErrors($this->_errors);

            return false;
        }

        return $this;
    }


    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        return [
            self::PACKAGE_TYPE => __('Package')
        ];
    }

    /**
     * Map request to shipment
     *
     * @param \Magento\Framework\DataObject $request
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _mapRequestToShipment(\Magento\Framework\DataObject $request)
    {
        $request->setOrigCountryId($request->getShipperAddressCountryCode());
        $this->setRawRequest($request);
        $customsValue = 0;
        $packageWeight = 0;
        $packages = $request->getPackages();
        foreach ($packages as &$piece) {
            $params = $piece['params'];
            if ($params['width'] || $params['length'] || $params['height']) {
                $minValue = $this->_getMinDimension($params['dimension_units']);
                if ($params['width'] < $minValue || $params['length'] < $minValue || $params['height'] < $minValue) {
                    $message = __('Height, width and length should be equal or greater than %1', $minValue);
                    throw new \Magento\Framework\Exception\LocalizedException($message);
                }
            }

            $weightUnits = $piece['params']['weight_units'];
            $piece['params']['height'] = $this->_getDimension($piece['params']['height'], $weightUnits);
            $piece['params']['length'] = $this->_getDimension($piece['params']['length'], $weightUnits);
            $piece['params']['width'] = $this->_getDimension($piece['params']['width'], $weightUnits);
            $piece['params']['dimension_units'] = $this->_getDimensionUnit();
            $piece['params']['weight'] = $this->_getWeight($piece['params']['weight'], false, $weightUnits);
            $piece['params']['weight_units'] = $this->_getWeightUnit();

            $customsValue += $piece['params']['customs_value'];
            $packageWeight += $piece['params']['weight'];
        }

        $request->setPackages($packages)
            ->setPackageWeight($packageWeight)
            ->setPackageValue($customsValue)
            ->setValueWithDiscount($customsValue)
            ->setPackageCustomsValue($customsValue)
            ->setFreeMethodWeight(0);
    }

    /**
     * Retrieve minimum allowed value for dimensions in given dimension unit
     *
     * @param string $dimensionUnit
     * @return int
     */
    protected function _getMinDimension($dimensionUnit)
    {
        return 1;
    }

    /**
     * Do rate request and handle errors
     *
     * @return Result|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _doRequest()
    {
        $rawRequest = $this->_request;

        $originRegion = $this->getCountryParams(
            $this->_scopeConfig->getValue(
                MageShipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            )
        )->getRegion();

        if (!$originRegion) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Wrong Region'));
        }

        if ($originRegion == 'AM') {
            $originRegion = '';
        }

        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<req:ShipmentValidateRequest' .
            $originRegion .
            ' xmlns:req="http://www.dhl.com"' .
            ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
            ' xsi:schemaLocation="http://www.dhl.com ship-val-req' .
            ($originRegion ? '_' .
                $originRegion : '') .
            '.xsd" />';
        $xml = $this->_xmlElFactory->create(['data' => $xmlStr]);

        $nodeRequest = $xml->addChild('Request', '', '');
        $nodeServiceHeader = $nodeRequest->addChild('ServiceHeader');
        $nodeServiceHeader->addChild('SiteID', (string)$this->getConfigData('id'));
        $nodeServiceHeader->addChild('Password', (string)$this->getConfigData('password'));

        if (!$originRegion) {
            $xml->addChild('RequestedPickupTime', 'N', '');
        }
        $xml->addChild('NewShipper', 'N', '');
        $xml->addChild('LanguageCode', 'EN', '');
        $xml->addChild('PiecesEnabled', 'Y', '');

        /** Billing */
        $nodeBilling = $xml->addChild('Billing', '', '');
        $nodeBilling->addChild('ShipperAccountNumber', (string)$this->getConfigData('account'));
        /**
         * Method of Payment:
         * S (Shipper)
         * R (Receiver)
         * T (Third Party)
         */
        $nodeBilling->addChild('ShippingPaymentType', 'S');

        /**
         * Shipment bill to account – required if Shipping PaymentType is other than 'S'
         */
        $nodeBilling->addChild('BillingAccountNumber', (string)$this->getConfigData('account'));
        $nodeBilling->addChild('DutyPaymentType', 'S');
        $nodeBilling->addChild('DutyAccountNumber', (string)$this->getConfigData('account'));

        /** Receiver */
        $nodeConsignee = $xml->addChild('Consignee', '', '');

        $companyName = $rawRequest->getRecipientContactCompanyName() ? $rawRequest
            ->getRecipientContactCompanyName() : $rawRequest
            ->getRecipientContactPersonName();

        $nodeConsignee->addChild('CompanyName', substr($companyName, 0, 35));

        $address = $rawRequest->getRecipientAddressStreet1() . ' ' . $rawRequest->getRecipientAddressStreet2();
        $address = $this->string->split($address, 35, false, true);
        if (is_array($address)) {
            foreach ($address as $addressLine) {
                $nodeConsignee->addChild('AddressLine', $addressLine);
            }
        } else {
            $nodeConsignee->addChild('AddressLine', $address);
        }

        $nodeConsignee->addChild('City', $rawRequest->getRecipientAddressCity());
        $nodeConsignee->addChild('Division', $rawRequest->getRecipientAddressStateOrProvinceCode());
        $nodeConsignee->addChild('PostalCode', $rawRequest->getRecipientAddressPostalCode());
        $nodeConsignee->addChild('CountryCode', $rawRequest->getRecipientAddressCountryCode());
        $nodeConsignee->addChild(
            'CountryName',
            $this->getCountryParams($rawRequest->getRecipientAddressCountryCode())->getName()
        );
        $nodeContact = $nodeConsignee->addChild('Contact');
        $nodeContact->addChild('PersonName', substr($rawRequest->getRecipientContactPersonName(), 0, 34));
        $nodeContact->addChild('PhoneNumber', substr($rawRequest->getRecipientContactPhoneNumber(), 0, 24));

        /**
         * Commodity
         * The CommodityCode element contains commodity code for shipment contents. Its
         * value should lie in between 1 to 9999.This field is mandatory.
         */
        $nodeCommodity = $xml->addChild('Commodity', '', '');
        $nodeCommodity->addChild('CommodityCode', '1');

        /** Dutiable */
        if ($this->isDutiable(
            $rawRequest->getShipperAddressCountryCode(),
            $rawRequest->getRecipientAddressCountryCode()
        )) {
            $nodeDutiable = $xml->addChild('Dutiable', '', '');
            $nodeDutiable->addChild(
                'DeclaredValue',
                sprintf("%.2F", $rawRequest->getOrderShipment()->getOrder()->getSubtotal())
            );
            $baseCurrencyCode = $this->_storeManager->getWebsite($rawRequest->getWebsiteId())->getBaseCurrencyCode();
            $nodeDutiable->addChild('DeclaredCurrency', $baseCurrencyCode);
        }

        /**
         * Reference
         * This element identifies the reference information. It is an optional field in the
         * shipment validation request. Only the first reference will be taken currently.
         */
        $nodeReference = $xml->addChild('Reference', '', '');
        $nodeReference->addChild('ReferenceID', 'shipment reference');
        $nodeReference->addChild('ReferenceType', 'St');

        /** Shipment Details */
        $this->_shipmentDetails($xml, $rawRequest, $originRegion);

        /** Shipper */
        $nodeShipper = $xml->addChild('Shipper', '', '');
        $nodeShipper->addChild('ShipperID', (string)$this->getConfigData('account'));
        $nodeShipper->addChild('CompanyName', $rawRequest->getShipperContactCompanyName());
        $nodeShipper->addChild('RegisteredAccount', (string)$this->getConfigData('account'));

        $address = $rawRequest->getShipperAddressStreet1() . ' ' . $rawRequest->getShipperAddressStreet2();
        $address = $this->string->split($address, 35, false, true);
        if (is_array($address)) {
            foreach ($address as $addressLine) {
                $nodeShipper->addChild('AddressLine', $addressLine);
            }
        } else {
            $nodeShipper->addChild('AddressLine', $address);
        }

        $nodeShipper->addChild('City', $rawRequest->getShipperAddressCity());
        $nodeShipper->addChild('Division', $rawRequest->getShipperAddressStateOrProvinceCode());
        $nodeShipper->addChild('PostalCode', $rawRequest->getShipperAddressPostalCode());
        $nodeShipper->addChild('CountryCode', $rawRequest->getShipperAddressCountryCode());
        $nodeShipper->addChild(
            'CountryName',
            $this->getCountryParams($rawRequest->getShipperAddressCountryCode())->getName()
        );
        $nodeContact = $nodeShipper->addChild('Contact', '', '');
        $nodeContact->addChild('PersonName', substr($rawRequest->getShipperContactPersonName(), 0, 34));
        $nodeContact->addChild('PhoneNumber', substr($rawRequest->getShipperContactPhoneNumber(), 0, 24));

        $xml->addChild('LabelImageFormat', 'PDF', '');

        $request = $xml->asXML();
        if (!$request && !mb_detect_encoding($request) == 'UTF-8') {
            $request = utf8_encode($request);
        }

        $responseBody = $this->_getCachedQuotes($request);
        if ($responseBody === null) {
            $debugData = ['request' => $request];
            try {
                $client = $this->_httpClientFactory->create();
                $client->setUri((string)$this->getConfigData('gateway_url'));
                $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
                $client->setRawData($request);
                $responseBody = $client->request(\Magento\Framework\HTTP\ZendClient::POST)->getBody();
                $responseBody = utf8_decode($responseBody);
                $debugData['result'] = $responseBody;
                $this->_setCachedQuotes($request, $responseBody);
            } catch (\Exception $e) {
                $this->_errors[$e->getCode()] = $e->getMessage();
                $responseBody = '';
            }
            $this->_debug($debugData);
        }
        $this->_isShippingLabelFlag = true;

        return $this->_parseResponse($responseBody);
    }

    /**
     * Generation Shipment Details Node according to origin region
     *
     * @param \Magento\Shipping\Model\Simplexml\Element $xml
     * @param RateRequest $rawRequest
     * @param string $originRegion
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _shipmentDetails($xml, $rawRequest, $originRegion = '')
    {
        $nodeShipmentDetails = $xml->addChild('ShipmentDetails', '', '');
        $nodeShipmentDetails->addChild('NumberOfPieces', count($rawRequest->getPackages()));

        if ($originRegion) {
            $nodeShipmentDetails->addChild(
                'CurrencyCode',
                $this->_storeManager->getWebsite($this->_request->getWebsiteId())->getBaseCurrencyCode()
            );
        }

        $nodePieces = $nodeShipmentDetails->addChild('Pieces', '', '');

        /*
         * Package type
         * EE (DHL Express Envelope), OD (Other DHL Packaging), CP (Custom Packaging)
         * DC (Document), DM (Domestic), ED (Express Document), FR (Freight)
         * BD (Jumbo Document), BP (Jumbo Parcel), JD (Jumbo Junior Document)
         * JP (Jumbo Junior Parcel), PA (Parcel), DF (DHL Flyer)
         */
        $i = 0;
        foreach ($rawRequest->getPackages() as $package) {
            $nodePiece = $nodePieces->addChild('Piece', '', '');
            $packageType = 'EE';
            if ($package['params']['container'] == self::DHL_CONTENT_TYPE_NON_DOC) {
                $packageType = 'CP';
            }
            $nodePiece->addChild('PieceID', ++$i);
            $nodePiece->addChild('PackageType', $packageType);
            $nodePiece->addChild('Weight', round($package['params']['weight'], 1));
            $params = $package['params'];
            if ($params['width'] && $params['length'] && $params['height']) {
                if (!$originRegion) {
                    $nodePiece->addChild('Width', round($params['width']));
                    $nodePiece->addChild('Height', round($params['height']));
                    $nodePiece->addChild('Depth', round($params['length']));
                } else {
                    $nodePiece->addChild('Depth', round($params['length']));
                    $nodePiece->addChild('Width', round($params['width']));
                    $nodePiece->addChild('Height', round($params['height']));
                }
            }
            $content = [];
            foreach ($package['items'] as $item) {
                $content[] = $item['name'];
            }
            $nodePiece->addChild('PieceContents', substr(implode(',', $content), 0, 34));
        }

        if (!$originRegion) {
            $nodeShipmentDetails->addChild('Weight', round($rawRequest->getPackageWeight(), 1));
            $nodeShipmentDetails->addChild('WeightUnit', substr($this->_getWeightUnit(), 0, 1));
            $nodeShipmentDetails->addChild('GlobalProductCode', $rawRequest->getShippingMethod());
            $nodeShipmentDetails->addChild('LocalProductCode', $rawRequest->getShippingMethod());
            $nodeShipmentDetails->addChild('Date', $this->_coreDate->date('Y-m-d'));
            $nodeShipmentDetails->addChild('Contents', 'DHL Parcel');
            /**
             * The DoorTo Element defines the type of delivery service that applies to the shipment.
             * The valid values are DD (Door to Door), DA (Door to Airport) , AA and DC (Door to
             * Door non-compliant)
             */
            $nodeShipmentDetails->addChild('DoorTo', 'DD');
            $nodeShipmentDetails->addChild('DimensionUnit', substr($this->_getDimensionUnit(), 0, 1));
            if ($package['params']['container'] == self::DHL_CONTENT_TYPE_NON_DOC) {
                $packageType = 'CP';
            }
            $nodeShipmentDetails->addChild('PackageType', $packageType);
            if ($this->isDutiable($rawRequest->getOrigCountryId(), $rawRequest->getDestCountryId())) {
                $nodeShipmentDetails->addChild('IsDutiable', 'Y');
            }
            $nodeShipmentDetails->addChild(
                'CurrencyCode',
                $this->_storeManager->getWebsite($this->_request->getWebsiteId())->getBaseCurrencyCode()
            );
        } else {
            if ($package['params']['container'] == self::DHL_CONTENT_TYPE_NON_DOC) {
                $packageType = 'CP';
            }
            $nodeShipmentDetails->addChild('PackageType', $packageType);
            $nodeShipmentDetails->addChild('Weight', $rawRequest->getPackageWeight());
            $nodeShipmentDetails->addChild('DimensionUnit', substr($this->_getDimensionUnit(), 0, 1));
            $nodeShipmentDetails->addChild('WeightUnit', substr($this->_getWeightUnit(), 0, 1));
            $nodeShipmentDetails->addChild('GlobalProductCode', $rawRequest->getShippingMethod());
            $nodeShipmentDetails->addChild('LocalProductCode', $rawRequest->getShippingMethod());

            /**
             * The DoorTo Element defines the type of delivery service that applies to the shipment.
             * The valid values are DD (Door to Door), DA (Door to Airport) , AA and DC (Door to
             * Door non-compliant)
             */
            $nodeShipmentDetails->addChild('DoorTo', 'DD');
            $nodeShipmentDetails->addChild('Date', $this->_coreDate->date('Y-m-d'));
            $nodeShipmentDetails->addChild('Contents', 'DHL Parcel TEST');
        }
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->_getXMLTracking($trackings);

        return $this->_result;
    }

    /**
     * Send request for tracking
     *
     * @param string[] $trackings
     * @return void
     */
    protected function _getXMLTracking($trackings)
    {
        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<req:KnownTrackingRequest' .
            ' xmlns:req="http://www.dhl.com"' .
            ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
            ' xsi:schemaLocation="http://www.dhl.com TrackingRequestKnown.xsd" />';

        $xml = $this->_xmlElFactory->create(['data' => $xmlStr]);

        $requestNode = $xml->addChild('Request', '', '');
        $serviceHeaderNode = $requestNode->addChild('ServiceHeader', '', '');
        $serviceHeaderNode->addChild('SiteID', (string)$this->getConfigData('id'));
        $serviceHeaderNode->addChild('Password', (string)$this->getConfigData('password'));

        $xml->addChild('LanguageCode', 'EN', '');
        foreach ($trackings as $tracking) {
            $xml->addChild('AWBNumber', $tracking, '');
        }
        /**
         * Checkpoint details selection flag
         * LAST_CHECK_POINT_ONLY
         * ALL_CHECK_POINTS
         */
        $xml->addChild('LevelOfDetails', 'ALL_CHECK_POINTS', '');

        /**
         * Value that indicates for getting the tracking details with the additional
         * piece details and its respective Piece Details, Piece checkpoints along with
         * Shipment Details if queried.
         *
         * S-Only Shipment Details
         * B-Both Shipment & Piece Details
         * P-Only Piece Details
         * Default is ‘S’
         */
        //$xml->addChild('PiecesEnabled', 'ALL_CHECK_POINTS');

        $request = $xml->asXML();
        $request = utf8_encode($request);

        $responseBody = $this->_getCachedQuotes($request);
        if ($responseBody === null) {
            $debugData = ['request' => $request];
            try {
                $client = new \Magento\Framework\HTTP\ZendClient();
                $client->setUri((string)$this->getConfigData('gateway_url'));
                $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
                $client->setRawData($request);
                $responseBody = $client->request(\Magento\Framework\HTTP\ZendClient::POST)->getBody();
                $debugData['result'] = $responseBody;
                $this->_setCachedQuotes($request, $responseBody);
            } catch (\Exception $e) {
                $this->_errors[$e->getCode()] = $e->getMessage();
                $responseBody = '';
            }
            $this->_debug($debugData);
        }

        $this->_parseXmlTrackingResponse($trackings, $responseBody);
    }

    /**
     * Parse xml tracking response
     *
     * @param string[] $trackings
     * @param string $response
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _parseXmlTrackingResponse($trackings, $response)
    {
        $errorTitle = __('Unable to retrieve tracking');
        $resultArr = [];

        if (strlen(trim($response)) > 0) {
            $xml = $this->parseXml($response, 'Magento\Shipping\Model\Simplexml\Element');
            if (!is_object($xml)) {
                $errorTitle = __('Response is in the wrong format');
            }
            if (is_object($xml)
                && (isset($xml->Response->Status->ActionStatus)
                    && $xml->Response->Status->ActionStatus == 'Failure'
                    || isset($xml->GetQuoteResponse->Note->Condition))
            ) {
                if (isset($xml->Response->Status->Condition)) {
                    $nodeCondition = $xml->Response->Status->Condition;
                }
                $code = isset($nodeCondition->ConditionCode) ? (string)$nodeCondition->ConditionCode : 0;
                $data = isset($nodeCondition->ConditionData) ? (string)$nodeCondition->ConditionData : '';
                $this->_errors[$code] = __('Error #%1 : %2', $code, $data);
            } elseif (is_object($xml) && is_object($xml->AWBInfo)) {
                foreach ($xml->AWBInfo as $awbinfo) {
                    $awbinfoData = [];
                    $trackNum = isset($awbinfo->AWBNumber) ? (string)$awbinfo->AWBNumber : '';
                    if (!is_object($awbinfo) || !$awbinfo->ShipmentInfo) {
                        $this->_errors[$trackNum] = __('Unable to retrieve tracking');
                        continue;
                    }
                    $shipmentInfo = $awbinfo->ShipmentInfo;

                    if ($shipmentInfo->ShipmentDesc) {
                        $awbinfoData['service'] = (string)$shipmentInfo->ShipmentDesc;
                    }

                    $awbinfoData['weight'] = (string)$shipmentInfo->Weight . ' ' . (string)$shipmentInfo->WeightUnit;

                    $packageProgress = [];
                    if (isset($shipmentInfo->ShipmentEvent)) {
                        foreach ($shipmentInfo->ShipmentEvent as $shipmentEvent) {
                            $shipmentEventArray = [];
                            $shipmentEventArray['activity'] = (string)$shipmentEvent->ServiceEvent->EventCode
                                . ' ' . (string)$shipmentEvent->ServiceEvent->Description;
                            $shipmentEventArray['deliverydate'] = (string)$shipmentEvent->Date;
                            $shipmentEventArray['deliverytime'] = (string)$shipmentEvent->Time;
                            $shipmentEventArray['deliverylocation'] = (string)$shipmentEvent->ServiceArea
                                ->Description . ' [' . (string)$shipmentEvent->ServiceArea->ServiceAreaCode . ']';
                            $packageProgress[] = $shipmentEventArray;
                        }
                        $awbinfoData['progressdetail'] = $packageProgress;
                    }
                    $resultArr[$trackNum] = $awbinfoData;
                }
            }
        }

        $result = $this->_trackFactory->create();

        if (!empty($resultArr)) {
            foreach ($resultArr as $trackNum => $data) {
                $tracking = $this->_trackStatusFactory->create();
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($trackNum);
                $tracking->addData($data);
                $result->append($tracking);
            }
        }

        if (!empty($this->_errors) || empty($resultArr)) {
            $resultArr = !empty($this->_errors) ? $this->_errors : $trackings;
            foreach ($resultArr as $trackNum => $err) {
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking(!empty($this->_errors) ? $trackNum : $err);
                $error->setErrorMessage(!empty($this->_errors) ? $err : $errorTitle);
                $result->append($error);
            }
        }

        $this->_result = $result;
    }

    /**
     * Get final price for shipping method with handling fee per package
     *
     * @param float $cost
     * @param string $handlingType
     * @param float $handlingFee
     * @return float
     */
    protected function _getPerpackagePrice($cost, $handlingType, $handlingFee)
    {
        if ($handlingType == AbstractCarrier::HANDLING_TYPE_PERCENT) {
            return $cost + $cost * $this->_numBoxes * $handlingFee / 100;
        }

        return $cost + $this->_numBoxes * $handlingFee;
    }

    /**
     * Do request to shipment
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return array|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToShipment($request)
    {

        // @NOTE we don't make labels here; magento
        $response = new \Magento\Framework\DataObject(
            [
                'info' => [
                    [
                        'tracking_number' => false,
                        'label_content' => false,
                    ],
                ],
            ]
        );

        return $response;

        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new \Magento\Framework\Exception\LocalizedException(__('No packages for request'));
        }
        $result = $this->_doShipmentRequest($request);

        $response = new \Magento\Framework\DataObject(
            [
                'info' => [
                    [
                        'tracking_number' => $result->getTrackingNumber(),
                        'label_content' => $result->getShippingLabelContent(),
                    ],
                ],
            ]
        );

        $request->setMasterTrackingId($result->getTrackingNumber());

        return $response;
    }

    /**
     * Check if shipping is domestic
     *
     * @param string $origCountryCode
     * @param string $destCountryCode
     * @return bool
     */
    protected function _checkDomesticStatus($origCountryCode, $destCountryCode)
    {
        $this->_isDomestic = false;

        $origCountry = (string)$this->getCountryParams($origCountryCode)->getData('name');
        $destCountry = (string)$this->getCountryParams($destCountryCode)->getData('name');
        $isDomestic = (string)$this->getCountryParams($destCountryCode)->getData('domestic');

        if (($origCountry == $destCountry && $isDomestic)
            || ($this->_carrierHelper->isCountryInEU($origCountryCode)
                && $this->_carrierHelper->isCountryInEU($destCountryCode)
            )
        ) {
            $this->_isDomestic = true;
        }

        return $this->_isDomestic;
    }

    /**
     * Prepare shipping label data
     *
     * @param \SimpleXMLElement $xml
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareShippingLabelContent(\SimpleXMLElement $xml)
    {
        throw new \Magento\Framework\Exception\LocalizedException(__('Module does not support labels. Please contact Collins Harper for support.'));

    }



    //TODO break the error code and fail over out of here into its ownfunction
    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return bool|Result|Error
     */
    public function collectRates(RateRequest $request)
    {

        if (!$this->isActive()) {
            return false;
        }

        // we cannot get rates without
        if(!$request->getDestPostcode() || !$request->getDestCountryId()) {
            return false;
        }

        $result = $this->_rateFactory->create();


        // TODO abstract these to the helper always check the current store scope ? order shipment?
        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                MageShipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }

        $request->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));


        // TODO abstract these to the helper always check the current store scope ? order shipment?
        if ($request->getOrigPostcode()) {
            $request->setOrigPostal($request->getOrigPostcode());
        } else {
            $request->setOrigPostal(
                $this->_scopeConfig->getValue(
                    MageShipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        $this->_rawRequest = $request;

        $backOrderSkipDeliveryEstimate = $this->shouldSkipBackOrder();
        $this->_rawRequest->setNoEstimateDate($backOrderSkipDeliveryEstimate);


        // SHANE MAGICAL BEANS


        $packages = $this->getCpHelper()->getBoxForItems($request->getAllItems());

        if (!isset($packages['error'])) {
            $rates = $this->getIntersectRates($packages, $request);
        } else {
            // TODO log exception somehow
            //Mage::logException(new Exception("Could not pack items into boxes due to error: '{$packages['error']}'"));

            // tODO dodes this still apply?
            if(1 || $this->getConfigValue(CPHelper::XML_PATH_NOTIFY_ERROR_SHOW)) {
                // TODO check table rates for failure rate per couintry?
                $errorMsg = $this->getConfigData('specificerrmsg');

                if (!$errorMsg) {
                    $errorMsg = __("The items you have selected cannot be shipped at the moment.  Please contact the store owner to arrange for shipping.");
                }

                $error = $this->_rateErrorFactory->create();
                $error->setCarrier(self::CODE);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($errorMsg);
                $result->append($error);
                return $result;
            }


        }

        $free_methods = explode(',', $this->getConfigValue('carriers/cpcanadapost/free_method'));
        $enable_order = $this->getConfigValue('carriers/cpcanadapost/free_shipping_enable');
        $order_amount = $this->getConfigValue('carriers/cpcanadapost/free_shipping_subtotal');

        // tODO verify we have the data to restrict by product and use it
        $rates = $this->restrictRatesByProduct($rates, $request);

        $subtotal = $request->getPackageValueWithDiscount();


        if (!empty($rates) && is_array($rates)) {

            foreach ($rates as $rate) {

                if($rate['code'] == 'failure'){
                    $errorMsg = $this->getConfigData('specificerrmsg');
                    $error1 = $this->_rateErrorFactory->create();
                    $error1->setCarrier(self::CODE);
                    $error1->setCarrierTitle($this->getConfigData('title'));
                    $error1->setErrorMessage($errorMsg);
                    return $error1;
                } 
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);

                $fullTitle = $rate['method']; //$this->getServiceTitle($rate['code']);


                if ($request->getFreeShipping() || $request->getPackageQty() == $this->getFreeBoxes() || ($enable_order
                    && in_array($rate['code'], $free_methods)
                    && (float) $subtotal >= $order_amount)) {
                    $qualifiesForFlatRateOrFree = true;
                    $shippingPrice = '0.00';
                }

                if (!empty($rate['expected-delivery-date']) && $this->getConfigData('show_delivery_date') && !$backOrderSkipDeliveryEstimate) {

                    $date = $this->getCpHelper()->formatDate($rate['expected-delivery-date']);

                    $fullTitle = __('%1 - Est. Delivery %2  ', $fullTitle, $date);

                }

                $returnRate = $this->_rateMethodFactory->create();
                $returnRate->setCarrier(self::CODE);
                $returnRate->setCarrierTitle($this->getConfigData('title'));
                $returnRate->setMethod($rate['code']);
                $returnRate->setMethodTitle($fullTitle);
                $returnRate->setCost($rate['price']);
                $returnRate->setPrice($shippingPrice);
                $result->append($returnRate);
                
                
            }

        } else if (!empty($this->fail_reason) && $this->fail_reason == self::WEIGHT_FAILURE) {

            // TODO log the error

        }

        return $result;


        // SHANE MAGICAL BEANS
    }




    /**
     * Gets all Canada Post rates available for this order.
     *
     * @param array $data An assoc. array representing the API request payload.
     *
     * @return array
     */
    private function getCpRates($data, $request)
    {
      // TODO if we EVER allow frontend users to select require signature ; we would need to load the quote here and get the params they selected.
      // 'chcanpost2module/quote_param')->getParamsByQuote

        if ($request->getDestCountryId() != CPHelper::COUNTRY_CANADA) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getWeight() == 0) {
                    $this->fail_reason = self::WEIGHT_FAILURE;
                    return array();
                }
            }
        }


        // TODO we need to rethink the require options and they all need to be composed on one place.
        if ($this->getConfigData('require_coverage') == \CollinsHarper\CanadaPost\Model\Source\Coverage::ALWAYS
            || $this->getConfigData('require_coverage') == \CollinsHarper\CanadaPost\Model\Source\Coverage::NEVER
        ) {

            $data[CPOption::OPTIONS_COVERAGE] = 0;

        }


        if (isset($service_code) && !empty($service_code)) {

            $data['services'] = array($service_code);

        }

        $rates = $this->getRateApi()->getRates($data);


        if (empty($rates)) {

            $data['weight'] = self::WEIGHT_FAILURE_TEST;

            $rates = $this->getRateApi()->getRates($data);

            if (!empty($rates)) {

                $this->fail_reason = self::WEIGHT_FAILURE;

                $rates = array();

            }

        }

        if (!empty($rates) && is_array($rates) && $this->getConfigData('require_coverage') == \CollinsHarper\CanadaPost\Model\Source\Coverage::ALWAYS) {

            $data[CPOption::OPTIONS_COVERAGE] = 1;

            $countryCode = $request->getDestCountryId() ? $request->getDestCountryId() : CPHelper::COUNTRY_CANADA;

            foreach ($rates as $key => &$rate) {

                // @note if a merchant has select require coverage and the rate doesnt support it - remove it.
                $maxCoverage = $this->getOptionHelper()->getMaxCoverage($rate['code'], $countryCode);
                if (empty($maxCoverage)) {
                    unset($rates[$key]);
                    continue;
                }

                $data[CPOption::OPTIONS_COVERAGE_AMOUNT] = min($request->getBaseSubtotalInclTax(), $maxCoverage);

                $data['services'] = array($rate['code']);

                $updatedRates = $this->getRateApi()->getRates($data);
                $updatedRate = reset($updatedRates);  // Get the first rate, since there should be only one.

                if (!empty($updatedRate['price'])) {
                    //expect to see only one rate for one service
                    $rate['price'] = $updatedRate['price'];
                } else {
                    // Service rate does not support coverage, so remove it from available options.
                    unset($rates[$key]);
                    continue;
                }

            }

        }

        return $rates;

    }


    // TODO legacy code needs to be redone the "mage2" way

    /**
     * Makes an API request to get shipping rates for the given packages.
     *
     * @param array                            $pack    An assoc. array representing the package to send.
     * @param Mage_Shipping_Model_Rate_Request $request The shipping rate request object.
     *
     * @return array
     */
    private function getPackRates($pack, $request)
    {

        $weight = (!empty($pack['box'])) ? $pack['box']['weight'] : 0;

        if (!empty($pack['items'])) {

            foreach ($pack['items'] as $item) {

                $weight += $item['weight'];

            }

        }

        // TODO why is this nere and not done with constants?
        $data = array(
            'xmlns' => 'http://www.canadapost.ca/ws/ship/rate',
            'weight' => (!empty($pack['weight'])) ? $pack['weight'] : $weight,
            'postal-code' => $request->getDestPostcode(),
            'country_code' => $request->getDestCountryId(),
            'box' => (!empty($pack['box'])) ? $pack['box'] : array(),
        );

        return $this->getCpRates($data, $request);

    }


    /**
     * getIntersectRates
     *
     * @param array                            $packages An array of package data for shipping.
     * @param Mage_Shipping_Model_Rate_Request $request  The shipping rate request object.
     *
     * @return array
     */
    private function getIntersectRates($packages, $request)
    {

        $pack_rates = array();

        if (!empty($packages)) {

            foreach ($packages as $i => $pack) {

                $pack_rates[] = $this->getPackRates($pack, $request);

            }

        }

        $rates = array();

        $rates_counter = array();

        foreach ($pack_rates as $set_rates) {

            foreach ($set_rates as $rate) {

                $rates_counter[$rate['code']] = (!empty($rates_counter[$rate['code']])) ? $rates_counter[$rate['code']]+1: 1;

            }

        }

        foreach ($pack_rates as $set_rates) {

            foreach ($set_rates as $rate) {

                if (empty($rates[$rate['code']]) && $rates_counter[$rate['code']] == count($pack_rates)) {

                    $rates[$rate['code']] = $rate;

                } else if (!empty($rates[$rate['code']])) {

                    $rates[$rate['code']]['price'] += $rate['price'];

                }

            }

        }

        return $rates;

    }


    /** TODO NOT USED
     * Removes from $rates shipping methods that are not allowed by the product items in $quote
     * Uses cpv2 custom product attributes restrict_shipping_methods (boolean) and allowed_shipping_methods (comma-separated string).
     *
     * @param array $rates
     * @param Mage_Shipping_Model_Rate_Request $quote
     * @return array $rates
     */
    public function restrictRatesByProduct($rates, $request)
    {
        $products = array();
        foreach ($request->getAllItems() as $item) {
            // load the product to get attribute data
            $product = $this->getCpHelper()->getProduct($item);
            if (!$product->getRestrictShippingMethods()) {
                continue;
            }

            $allowedMethods = explode(',', $product->getAllowedShippingMethods());

            foreach ($rates as $code => $rate) {
                if (!in_array($code, $allowedMethods)) {
                    unset($rates[$code]);
                }
            }
        }

        return $rates;
    }

    /**
     * Determines whether there are back ordered items that should not get a delivery estimate.
     * @todo move all these things to the top; populate the request object with everythingk we need too know then run through rate
     * @param Mage_Shipping_Model_Rate_Request $request The shipping rate request object.
     *
     * @return boolean
     */
    public function shouldSkipBackOrder()
    {
        if (!$this->getConfigData('back_order_no_estimate')) {
            return false;
        }

        foreach ($this->_rawRequest->getAllItems() as $item) {

            $stockItem = $this->getCpHelper()->getStockItemByProductId($item->getProductId());
            $canBackOrder = $stockItem->getBackorders();
            $qty  = (int) $stockItem->getQty();

            // if it is out and it can back order. we want to flag this as not to show the date.
            if ($canBackOrder && $qty < 1) {
                return true;
            }
        }
        return false;
    }

    // TODO partial duplicate code from helper?
    public function returnFrench()
    {
        // TODO how do we get locale
        $isFrenchStore = $this->getConfigData('locale') &&
            stristr($this->getCpHelper()->getCurrentLocale(), CPHelper::LANG_MAGE_FR_PART);
        return $this->getConfigData('return_lang') || $isFrenchStore;

    }


    public function log($debugData, $force = false)
    {
        if($force) {
            $this->_logger->debug(var_export($debugData, true));
        } else {
            $this->_debug($debugData);
        }
    }

    public function getServiceTitle($code)
    {
        $map = $this->getServiceMap();
        return isset($map[$code]) ? $map[$code] : __(CPHelper::DEFAULT_SHIPPING_TITLE);
    }

    public function getServiceMap()
    {
        if(!$this->_service_map) {
            $this->_service_map = $this->objectFactory->create([], 'CollinsHarper\CanadaPost\Model\Source\Method\Lists')->getList();
        }
        return $this->_service_map;
    }

    public function getXmlAttribute($object, $attribute)
    {
        $part = $object->xpath($attribute);
        if(is_array($part) && isset($part[0])) {
            return (string) $part[0];
        }
        return false;
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

}
