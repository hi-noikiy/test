<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper;
use \Magento\Framework\App\Helper\Context;



use \Magento\Shipping\Model\Config;
use \Magento\Store\Model\Information;
use \Magento\Sales\Model\Order\Shipment;
use Magento\Framework\App\Config\ScopeConfigInterface;


/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Option extends \Magento\Framework\App\Helper\AbstractHelper
{

    const OPTIONS_DC = 'DC';
    const OPTIONS_SO = 'SO';
    const OPTIONS_COV = 'COV';
    const OPTIONS_LAD = 'LAD';
    const OPTIONS_D2PO = 'D2PO';
    const OPTIONS_COD = 'COS';
    const OPTIONS_DNS = 'DNS';
    const OPTIONS_HFP = 'HFP';
    const OPTIONS_PA_NONE = 'none';
    const OPTIONS_PA18 = 'PA18';
    const OPTIONS_PA19 = 'PA19';
    const OPTIONS_PA_PROV = 'by_province';
    const OPTIONS_PA18_LABEL = '18+';
    const OPTIONS_PA19_LABEL = '19+';
    const OPTIONS_PA_PROV_LABEL = '18+ or 19+ by province';
    const OPTIONS_RTS = 'RTS';
    const OPTIONS_RASE = 'RASE';
    const OPTIONS_ABAN = 'ABAN';
    const OPTIONS_COVERAGE = 'coverage';
    const OPTIONS_COVERAGE_AMOUNT = 'coverage_amount';


    /**
     *
     * @var array
     */
    protected $_conflicting_options = array(
        self::OPTIONS_DC => array('conflict' => array(), 'pre' => ''),
        self::OPTIONS_SO => array('conflict' => array(self::OPTIONS_LAD),
            'pre' => self::OPTIONS_DC),
        self::OPTIONS_COV => array('conflict' => array(),
            'pre' => self::OPTIONS_DC),
        self::OPTIONS_LAD => array('conflict' => array(self::OPTIONS_D2PO,
            self::OPTIONS_COD, self::OPTIONS_DNS, self::OPTIONS_HFP,
            self::OPTIONS_PA18, self::OPTIONS_PA19, self::OPTIONS_SO), 'pre' => ''),
        self::OPTIONS_D2PO => array('conflict' => array(self::OPTIONS_LAD,
            self::OPTIONS_HFP, self::OPTIONS_DNS, self::OPTIONS_COD), 'pre' => ''),
        self::OPTIONS_COD => array('conflict' => array(self::OPTIONS_D2PO,
            self::OPTIONS_LAD), 'pre' => self::OPTIONS_DC ),
        self::OPTIONS_DNS => array('conflict' => array(self::OPTIONS_PA18,
            self::OPTIONS_PA19, self::OPTIONS_D2PO, self::OPTIONS_HFP, self::OPTIONS_LAD), 'pre' => ''),
        self::OPTIONS_HFP => array('conflict' => array(self::OPTIONS_D2PO,
            self::OPTIONS_LAD, self::OPTIONS_DNS), 'pre' => ''),
        self::OPTIONS_PA18 => array('conflict' => array(self::OPTIONS_DNS,
            self::OPTIONS_PA19, self::OPTIONS_LAD), 'pre' => self::OPTIONS_SO),
        self::OPTIONS_PA19 => array('conflict' => array(self::OPTIONS_DNS,
            self::OPTIONS_PA18, self::OPTIONS_LAD), 'pre' => self::OPTIONS_SO),
    );
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_admin_quote;


    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkout_session;


    /**
     * @var \CollinsHarper\Core\Logger\Logger
     */
    protected $_chLogged;

    private $objectFactory;
    private $helperFactory;


    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param \CollinsHarper\Core\Logger\Logger $chLogged
     * @param \Magento\Backend\Model\Session\Quote $adminQuote
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \CollinsHarper\CanadaPost\Helper\DataFactory $helperFactory
     * @param \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory
     */
    public function __construct(
        Context $context,
        \CollinsHarper\Core\Logger\Logger $chLogged,
        \Magento\Backend\Model\Session\Quote $adminQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \CollinsHarper\CanadaPost\Helper\DataFactory $helperFactory,
        \CollinsHarper\CanadaPost\Model\ObjectFactory $objectFactory
    )
    {
        $this->_moduleManager = $context->getModuleManager();
        $this->_logger = $context->getLogger();
        $this->_chLogged = $chLogged;
        $this->productRepository = $productRepository;
        $this->objectFactory = $objectFactory;
        $this->helperFactory = $helperFactory;
        $this->_admin_quote = $adminQuote;
        $this->_checkout_session = $checkoutSession;
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

    /**
     * 
     * @param string $path
     * @param string $scopeType
     * @param string $scopeCode
     * @return mixed
     */
    public function getConfigValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * 
     * @return mixed
     */
    public function getRequireSignature()
    {
        return (int)$this->getConfigValue(\CollinsHarper\CanadaPost\Helper\AbstractHelp::XML_PATH_SIGNATURE);
    }

    /**
     * 
     * @return mixed
     */
    public function getRequireSignatureThreshold()
    {
        return $this->getConfigValue(\CollinsHarper\CanadaPost\Helper\AbstractHelp::XML_PATH_SIGNATURE_THRESHOLD);
    }



    /**
     * @param array $quote_params
     * @param Magento\Sales\Model\Order\Shipment $shipment
     * @param string $shipping_address
     * @param array $mandatory_options
     * @return string
     * @TODO fix hard coded Non-delivery handling codes
     */
    public function composeForOrder($quote_params, $shipment, $shipping_address, $mandatory_options = array(), $available_options = array())
    {
        $options = array();

        //check if global total require signature
        if ($this->getRequireSignature() && $shipment->getOrder()->getData('grand_total') > $this->getRequireSignatureThreshold()) {

            $options[self::OPTIONS_SO] = array();

        }

        //check if user requested signature
        if (!isset($options[self::OPTIONS_SO])) {

            $signature = $quote_params['signature'];

            if (!empty($signature)) {

                $options[self::OPTIONS_SO] = array();

            }

        }

        //check all order items if any of them require signature
        if (!isset($options[self::OPTIONS_SO])) {

            foreach ($shipment->getAllItems() as $item) {

                // TODO this should be testing the product NOT the item ; right?
                if ($item->getShipReqSignature()) {

                    $options[self::OPTIONS_SO] = array();

                }

            }

        }

        if ($quote_params['card_for_pickup']) {

            $options[self::OPTIONS_HFP] = array();

        }

        if ($quote_params['do_not_safe_drop']) {

            $options[self::OPTIONS_DNS] = array();

        }

        if ($quote_params['leave_at_door']) {

            $options[self::OPTIONS_LAD] = array();

        }

        if ($quote_params['coverage'] && !empty($quote_params['coverage_amount'])) {

            $options[self::OPTIONS_COV] = array('amount' => $quote_params['coverage_amount']);

        }

        $options = $this->checkAgeSensitiveProducts($shipment->getAllItems(), $shipping_address->getCountryId(), $shipping_address->getRegionCode(), $options);

        if (!empty($quote_params['office_id'])) {

            $office = $this->objectFactory->create('CollinsHarper\CanadaPost\Model\Office')->unsetData()->load($quote_params['office_id']);

            $options[self::OPTIONS_D2PO] = array(
                'option-qualifier-2' => $office->getCpOfficeId(),
            );

        }

        if (!empty($mandatory_options)) {

            foreach ($mandatory_options as $option_code) {

                if (!isset($options[$option_code])) {

                    $options[$option_code] = array();

                }

            }

        }

        $conflicted_options = array();

        foreach ($options as $code => $data) {

            $info = $this->getConflicts($code);

            if (!empty($info['conflict'])) {

                $conflicted_options = array_unique(array_merge($conflicted_options, $info['conflict']));

            }

        }

        //remove not available options
        foreach ($options as $code => $data) {

            if (!in_array($code, $available_options)) {

                unset($options[$code]);

            }

        }

        if (is_array($conflicted_options)) {

            //remove conflicted option, just double check
            foreach ($conflicted_options as $code) {

                unset($options[$code]);

            }

        }

        if (count($options) > 1) {
            $optionCodes = array_keys($options);
            // Check if this shipment is a non-delivery.
            if (count(array_intersect($optionCodes, array(self::OPTIONS_RTS, self::OPTIONS_RASE, self::OPTIONS_ABAN))) > 1) {
                $nondeliveryPreference = $this->scopeConfig->getValue(\CollinsHarper\CanadaPost\Helper\AbstractHelp::XML_PATH_NON_DELIVERY);
                // Check if the merchant's preference (could be RTS or ABAN) is one of the options ...
                if (in_array($nondeliveryPreference, $optionCodes)) {
                    // ... if so, choose it (by way of removing all others).
                    foreach ($options as $code => $data) {
                        if ($code != $nondeliveryPreference) {
                            unset($options[$code]);
                        }
                    }
                } else {
                    // ... if not, choose RASE by random default.
                    foreach ($options as $code => $data) {
                        if ($code != self::OPTIONS_RASE) {
                            unset($options[$code]);
                        }
                    }
                }
            }
        }

        return $this->formatOptions($options);

    }


    /**
     * 
     * @param bool $coverage
     * @param bool $signature
     * @param int $coverage_amount
     * @param bool $card_for_pickup
     * @param bool $do_not_safe_drop
     * @param bool $leave_at_door
     * @param bool $dc
     * @param bool $office_id
     * @return array
     */
    public function composeForCheckout(
        $coverage,
        $signature,
        $coverage_amount = 0,
        $card_for_pickup = false,
        $do_not_safe_drop = false,
        $leave_at_door = false,
        $dc = false,
        $office_id = 0)
    {

        $options = array();

        if ($dc) {

            $options[self::OPTIONS_DC] = array();

        }

        if ($signature) {

            $options[self::OPTIONS_SO] = array();

        }

        if ($coverage) {

            $options[self::OPTIONS_COV] = array('amount' => $coverage_amount);

        }

        if ($card_for_pickup) {

            $options[self::OPTIONS_HFP] = array();

        }

        if ($do_not_safe_drop) {

            $options[self::OPTIONS_DNS] = array();

        }

        if ($leave_at_door) {

            $options[self::OPTIONS_LAD] = array();

        }

        if (!empty($office_id)) {

            // TOOD  this was commented out from an old bug that alwasy trgiggered D2PO?
//            $options[self::OPTIONS_D2PO] = array(
//                'notification' => '',
//                'option-qualifier-2' => $office_id,
//            );

        }

        return $options;

    }


    /**
     * 
     * @param array $items
     * @param string $country_code
     * @param string $province_code
     * @param array $options
     * @return array
     */
    private function checkAgeSensitiveProducts($items, $country_code, $province_code, $options)
    {

        foreach ($items as $item) {

            $product = $this->getProduct($item);

            switch ($product->getData('ship_req_proof_of_age')) {

                case self::OPTIONS_PA18:

                    $options[self::OPTIONS_PA18] = array();

                    break;

                case self::OPTIONS_PA19:

                    $options[self::OPTIONS_PA19] = array();

                    break;

                case self::OPTIONS_PA_PROV:

                    if ($country_code == \CollinsHarper\CanadaPost\Helper\AbstractHelp::COUNTRY_CANADA) {

                        $options[$this->ageByProvince($province_code)] = array();

                    }

                    break;

                default:

            }

        }

        return $options;

    }

    /**
     * 
     * @param string $province_code
     * @return string
     */
    private function ageByProvince($province_code)
    {

        $pa = self::OPTIONS_PA18;

        switch ($province_code) {

            case 'BC':
            case 'NB':
            case 'NL':
            case 'NS':
            case 'NT':
            case 'YT':

                $pa = self::OPTIONS_PA19;

                break;

        }

        return $pa;

    }

    /**
     * 
     * @param array $options
     * @return string
     */
    private function formatOptions($options)
    {

        $xml = '';

        foreach ($options as $option_code=>$params) {

            if ($option_code == self::OPTIONS_D2PO) {

                $xml .= '<option><option-code>'.$option_code.'</option-code>';

                foreach ($params as $param=>$value) {

                    $xml .= '<'.$param.'>'.$value.'</'.$param.'>';

                }

                $xml .= '</option>';

            } else {

                $xml .= '<option><option-code>'.$option_code.'</option-code>';

                if (!empty($params['amount'])) {

                    $xml .= '<option-amount>'.number_format(str_replace(',', '', $params['amount']), 2, '.', '').'</option-amount>';

                }

                $xml .= '</option>';

            }

        }

        return $xml;

    }

    // TODO identify datatype of $option
    /**
     * 
     * @param type $option
     * @return array
     */
    public function getConflicts($option)
    {
        return (!empty($this->_conflicting_options[$option])) ? $this->_conflicting_options[$option] : array();

    }

    /**
     * 
     * @param string $type
     * @param Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function isPaOption($type = 'front', $quote = null)
    {

        if ($type == 'front') {

            $quote = $this->_checkout_session->getQuote();

        }

        $address = $quote->getShippingAddress();

        $options = $this->checkAgeSensitiveProducts($quote->getAllItems(), $address->getCountryId(), $address->getPostcode(), array());

        return (isset($options[self::OPTIONS_PA18]) || isset($options[self::OPTIONS_PA19]));

    }

    /**
     * 
     * @param array $selected_options
     * @return array
     */
    public function getConflictedOptions($selected_options)
    {

        $options = array();

        if ($selected_options['signature']) {

            $data = $this->getConflicts(self::OPTIONS_so);

            $options = array_merge($data['conflict'], $options);

        }

        if ($selected_options['coverage']) {

            $data = $this->getConflicts(self::OPTIONS_COV);

            $options = array_merge($data['conflict'], $options);

        }

        if ($selected_options['card_for_pickup']) {

            $data = $this->getConflicts(self::OPTIONS_HFP);

            $options = array_merge($data['conflict'], $options);

        }

        if ($selected_options['do_not_safe_drop']) {

            $data = $this->getConflicts(self::OPTIONS_DNS);

            $options = array_merge($data['conflict'], $options);

        }

        if ($selected_options['leave_at_door']) {

            $data = $this->getConflicts(self::OPTIONS_LAD);

            $options = array_merge($data['conflict'], $options);

        }

        if (!empty($selected_options['office_id'])) {

            $data = $this->getConflicts(self::OPTIONS_D2PO);

            $options = array_merge($data['conflict'], $options);

        }

        return array_unique($options);

    }

    /**
     * 
     * @param string $service_code
     * @param string $country_code
     * @return float
     */
    public function getMaxCoverage($service_code, $country_code)
    {

        // TODO cacne the max coverage values? cant we get this from the api specs?
        $amount = 0;

        $xml = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Rest\Service')->getInfo($service_code, $country_code);

        if (!empty($xml->options)) {

            foreach ($xml->options->option as $opt) {

                if ((string)$opt->{'option-code'} == self::OPTIONS_COV) {

                    $amount = (float)$opt->{'qualifier-max'};

                    break;

                }

            }

        }

        return $amount;

    }

    // TODO duplicate from abstract
    /**
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($item = null, $productId = null)
    {
        $product = null;
        if($item) {
            $product = $item->getProduct();
            if(!$product || $product->getId() != $item->getProductId()) {
                $productId = $item->getProductId();
            }
        }

        if(!$product && $productId) {
            $product = $this->productRepository->getById($productId);
        }

        return $product && $product->getId() ? $product : null;
    }

    /**
     * 
     * @param string $service_code
     * @param string $country_code
     * @return array
     */
    public function getAvailableOptions($service_code, $country_code)
    {

        $options = array();

        $xml = $this->helperFactory->create('CollinsHarper\CanadaPost\Helper\Rest\Service')->getInfo($service_code, $country_code);

        if (!empty($xml->options)) {

            foreach ($xml->options->option as $opt) {

                $amount = (float)$opt->{'qualifier-max'};

                $options[(string)$opt->{'option-code'}] = array(
                    'code' => (string)$opt->{'option-code'},
                    'max' => ((string)$opt->{'option-code'} == self::OPTIONS_COV) ? (float)$opt->{'qualifier-max'} : 0
                );

            }

        }

        return $options;

    }

}