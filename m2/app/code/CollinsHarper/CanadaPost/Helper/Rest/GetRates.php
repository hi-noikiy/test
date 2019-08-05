<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper\Rest;

use \Magento\Framework\App\Helper\Context;

/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetRates extends Request
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_cpService;

    /**
     * @var \CollinsHarper\CanadaPost\Helper\DataFactory
     */
    protected $dataFactory;

    /**
     * @var \CollinsHarper\CanadaPost\Helper\Option
     */
    protected $optionHelper;

    /**
     *
     * @var \Magento\Framework\App\CacheInterface 
     */
    protected $_cache;
    /**
     * @param Context $context
     * @param \CollinsHarper\Core\Logger\Logger $chLogged
     * @param \CollinsHarper\CanadaPost\Helper\Rest\Service $cpService
     * @param \CollinsHarper\CanadaPost\Helper\DataFactory $dataFactory
     */
    public function __construct(
        Context $context,
        \CollinsHarper\Core\Logger\Logger $chLogged,
        \CollinsHarper\CanadaPost\Helper\Rest\Service $cpService,
        \CollinsHarper\CanadaPost\Helper\DataFactory $dataFactory,
        \Magento\Framework\App\CacheInterface $cache            
    )
    {
        $this->_moduleManager = $context->getModuleManager();
        $this->_logger = $context->getLogger();
        $this->_chLogged = $chLogged;
        $this->_cpService = $cpService;
        $this->dataFactory = $dataFactory;
        $this->_request = $context->getRequest();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_httpHeader = $context->getHttpHeader();
        $this->_eventManager = $context->getEventManager();
        $this->_remoteAddress = $context->getRemoteAddress();
        $this->_cacheConfig = $context->getCacheConfig();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->urlDecoder = $context->getUrlDecoder();
        $this->scopeConfig = $context->getScopeConfig();
        $this->_cache = $cache;
    }


    // TODO the unit test consturction isnt setup right
    public function setDataFactory($factory)
    {
        $this->dataFactory = $factory;
    }
    public function setOptionHelper($model)
    {
        $this->optionHelper = $model;
    }

    public function getDataFactory()
    {
        return $this->dataFactory;
    }

    public function getOptionHelper()
    {
        if(!$this->optionHelper) {
            $this->optionHelper = $this->dataFactory->create('CollinsHarper\CanadaPost\Helper\Option');
        }
        return $this->optionHelper;
    }

    public function setChLogger($object)
    {
        $this->_chLogged = $object;
    }

    /**
     * API REST call Get Rates
     * 
     * @param array $data
     * @return array
     */
    public function getRates($requestData)
    {

        if($this->getConfigValue(self::XML_PATH_DEBUG_MODE)) {
            $this->_chLogged->info(__METHOD__ . __LINE__);
            $this->_chLogged->info(__METHOD__ . " request " . json_encode($requestData));
        }

        // TODO how do we access cache?
        if ($this->getConfigValue(self::XML_PATH_CACHE)) {
            $cacheKey = self::CACHE_KEY_PREFIX . md5(json_encode($requestData));
            $this->_chLogged->info(__METHOD__ . "cache key = {$cacheKey}");

            $cacheData = $this->_cache->load($cacheKey);
            if ($cacheData !== false) {
                $this->_chLogged->info(__METHOD__ . "No request, data from cache, cache key = {$cacheKey}");
                return json_decode($cacheData, true);
            }
        }

        $xml = $this->composeXml($requestData);

        $url = sprintf(self::API_PATH_RATE, $this->getBaseUrl());

        $response = $this->send($url, $xml, false, $this->_header_rate);




        if (!empty($response) && preg_match(self::PREG_RATE_RESPONSE, $response)) {

            $response_xml = new \SimpleXMLElement($response);

        }

        $data = array();

        if (!empty($response_xml)) {

            $allowed_methods = explode(',', $this->getConfigValue(self::XML_PATH_ALLOWED_METHODS));

            foreach ($response_xml->children() as $quote) {
                if (in_array((string)$quote->{'service-code'}, $allowed_methods)) {

                    // TODO: create setting to include this cost to flat rate (still needs tax, etc.) (and don't forget below):
                    //$optionsCost = 0;
                    //foreach ($quote->{'price-details'}->{'options'}->{'option'} as $option) {
                    //    $optionsCost += (float) $option->{'option-price'};
                    //}
                    $data[(string)$quote->{'service-code'}] = array(
                        'code' => (string)$quote->{'service-code'},
                        'expected-delivery-date' => (string)$quote->{'service-standard'}->{'expected-delivery-date'},
                        'method' => (string)$quote->{'service-name'},
                        'price'  => (float)$quote->{'price-details'}->{'due'},
                        //'options-cost' => (float) $optionsCost,
                    );

                }

            }

        } else {

            // TODO use table rates here
            $data['failure'] = array(
                'code' => 'failure',
                'expected-delivery-date' => false,
                'method' => 'failure',
                'price'  => 999
                //'options-cost' => 0
            );
        }


        // TODO DEAL WITH CACHE
        if ($this->getConfigValue(self::XML_PATH_CACHE) && !$this->error && !empty($data) && !isset($data['failure'])) {
            $this->_chLogged->info(__METHOD__ . "Response has been saved in cache");
            $cacheData = json_encode($data);
            $this->_cache->save($cacheData, $cacheKey, array(), self::CACHE_LIFETIME);
        }


        if($this->getConfigValue(self::XML_PATH_DEBUG_MODE)) {
            $this->_chLogged->info(__METHOD__ . __LINE__);
            $this->_chLogged->info(__METHOD__ . " response " . print_r($data, 1));
        }

        return $data;

    }


    /**
     * @param array $data
     *
     * @return string XML
     */
    public function composeXml($data)
    {

        $xml = new \DOMDocument;

        $xml->encoding = self::API_ENCODING;

        $xml->formatOutput = true;

        $scenario = $xml->createElement('mailing-scenario');

        $scenario->setAttribute('xmlns', $data['xmlns']);

        $contract_number = $this->getContractId();

        if ($this->getConfigValue(self::XML_PATH_QUOTE_TYPE) == self::QUOTE_COMMERCIAL) {

            $scenario->appendChild($xml->createElement('customer-number', $this->getApiCustomerNumber()));

            if (!empty($contract_number)) {

                $scenario->appendChild($xml->createElement('contract-id', $contract_number));

            }

            $scenario->appendChild($xml->createElement('quote-type', self::QUOTE_COMMERCIAL));

        } else {

            $scenario->appendChild($xml->createElement('quote-type', self::QUOTE_COUNTER));

        }

        if (!empty($data['services']) && is_array($data['services'])) {

            $services = $xml->createElement('services');

            foreach ($data['services'] as $service_code) {

                $services->appendChild($xml->createElement('service-code', $service_code));

            }

            $scenario->appendChild($services);

        }

        // TODO add lead times
        // if they specify a lead time we wioll add in a mailing date
        // if not lets leave it default to whatever CP does.
        //        $mailing_date = date('Y-m-d');
        if((int)$this->getConfigValue(self::XML_PATH_LEAD_TIME))
        {

            $_days = (int)$this->getConfigValue(self::XML_PATH_LEAD_TIME);

            // TODO use magento date?
            $mailing_date = date('Y-m-d', strtotime("+{$_days} days"));

            $scenario->appendChild($xml->createElement('expected-mailing-date', $mailing_date));

        }

        $scenario->appendChild($xml->createElement('origin-postal-code', $this->getStorePostcode()));

        // Parcel characteristics
        $parcel = $xml->createElement('parcel-characteristics');

        $parcel->appendChild($xml->createElement('weight', $data['weight']));

        $dim = $xml->createElement('dimensions');

        if (!empty($data['box'])) {

            $dim->appendChild($xml->createElement('length', $data['box']['l']));

            $dim->appendChild($xml->createElement('width', $data['box']['w']));

            $dim->appendChild($xml->createElement('height', $data['box']['h']));

        } else {

            $dim->appendChild($xml->createElement('length', $this->getDefaultLength()));

            $dim->appendChild($xml->createElement('width', $this->getDefaultWidth()));

            $dim->appendChild($xml->createElement('height', $this->getDefaultHeight()));

        }

        $parcel->appendChild($dim);

        $scenario->appendChild($parcel);

        // Destination parameters
        $destination = $xml->createElement('destination');

        switch ($data['country_code']) {

            case self::COUNTRY_USA:

                $us = $xml->createElement('united-states');

                $us->appendChild($xml->createElement('zip-code', $this->formatPostalCode($data['postal-code'])));

                $destination->appendChild($us);

                break;

            case self::COUNTRY_CANADA:

                $domestic = $xml->createElement('domestic');

                // TODO get offices working
                $postcode = $data['postal-code'];
                $postcode = (!empty($data['office_id'])) ? $this->_cpOfficeModel->unsetData()->load($data['office_id'])->getPostalCode() : $data['postal-code'];

                $domestic->appendChild($xml->createElement('postal-code',
                    str_replace(' ', '', $this->formatPostalCode($postcode))));

                $destination->appendChild($domestic);

                break;

            default:

                $international = $xml->createElement('international');

                $international->appendChild($xml->createElement('country-code', $data['country_code']));

                $destination->appendChild($international);

                break;

        }

        $scenario->appendChild($destination);

        $data['coverage'] = (!empty($data['coverage']));

        $data['signature'] = (!empty($data['signature']));

        $data['card_for_pickup'] = (!empty($data['card_for_pickup']));

        $data['do_not_safe_drop'] = (!empty($data['do_not_safe_drop']));

        $data['leave_at_door'] = (!empty($data['leave_at_door']));

        if (empty($data['coverage_amount'])) {

            $data['coverage_amount'] = 0;

        }

        if (!empty($data['services'][0])) {

            $service_info = $this->_cpService->getInfo($data['services'][0], $data['country_code']);

        }

        $data['dc'] = false;

        if (!empty($service_info->options->option)) {

            $mandatory_options = array();

            foreach ($service_info->options->option as $opt) {

                if (strtolower((string)$opt->mandatory) == 'true' && (string)$opt->{'option_code'} == 'DC') {

                    $data['dc'] = true;

                    break;

                }

            }

        }

        $options_data = $this->getOptionHelper()->composeForCheckout(
            $data['coverage'],
            $data['signature'],
            $data['coverage_amount'],
            $data['card_for_pickup'],
            $data['do_not_safe_drop'],
            $data['leave_at_door'],
            $data['dc'],
            isset($data['office_id']) ? $data['office_id'] : 0
        );

        if (!empty($options_data)) {

            $options = $xml->createElement('options');

            foreach ($options_data as $option_code => $params) {

                $option = $xml->createElement('option');

                $option->appendChild($xml->createElement('option-code', $option_code));

                if (!empty($params['amount'])) {

                    $option->appendChild($xml->createElement('option-amount',
                        number_format(str_replace(',', '', $params['amount']), 2, '.', '')));

                }

                $options->appendChild($option);

            }

            $scenario->appendChild($options);

        }

        $xml->appendChild($scenario);

        return $xml->saveXML();

    }

}