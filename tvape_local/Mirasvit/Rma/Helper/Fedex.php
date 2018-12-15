<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Helper_Fedex extends Mage_Core_Helper_Abstract
{
    /**
     * Current store.
     */
    protected $_store = null;

    /**
     * Current FedEx carrier
     */
    protected $_fedexCarrier = null;

    /**
     * Current FedEx SOAP client
     */
    protected $_fedexClient = null;

    /**
     * Returns default FedEx method from RMA config.
     *
     * @return string
     */
    public function getDefaultFedexMethod()
    {
        return strtoupper($this->getConfigData('fedex_method', false));
    }

    /**
     * Checks, whether FedEx service is properly enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfigData('fedex_enable', false) &&
           $this->getConfigData('key') != '' &&
           $this->getConfigData('password') != '' &&
           $this->getConfigData('account') != '' &&
           $this->getConfigData('meter_number') != '';
    }

    /**
     * Returns current store.
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (!$this->_store) {
            $this->_store = Mage::app()->getStore();
        }

        return $this->_store;
    }

    /**
     * Returns FedEx carrier, installed in current Magento environment.
     *
     * @return Mage_Usa_Model_Shipping_Carrier_Fedex
     */
    public function getFedexCarrier()
    {
        if ($this->_fedexCarrier === null) {
            $this->_fedexCarrier = new Mage_Usa_Model_Shipping_Carrier_Fedex();
        }

        return $this->_fedexCarrier;
    }

    /**
     * Returns SOAP client, used for communicating with FedEx server (either sandbox or production).
     *
     * @return Mage_Usa_Model_Shipping_Carrier_Fedex
     */
    public function getFedexClient()
    {
        $wsdl = Mage::getModuleDir('etc', 'Mage_Usa').DS.'wsdl'.DS.'FedEx'.DS.'ShipService_v10.wsdl';
        $sandboxMode = $this->getConfigData('sandbox_mode');
        if (!$this->_fedexClient) {
            $this->_fedexClient = new SoapClient($wsdl, array('trace' => 1));
            $this->_fedexClient->__setLocation($sandboxMode
                ? 'https://wsbeta.fedex.com:443/web-services '
                : 'https://ws.fedex.com:443/web-services'
            );

            return $this->_fedexClient;
        }

        return $this->_fedexClient;
    }

    /**
     * Sets current store.
     *
     * @param Mage_Core_Model_Store $store
     * @return void
     */
    public function setStore($store)
    {
        $this->_store = $store;
    }

    /**
     * Returns configuration constant by shortened key.
     *
     * @param string $key - shortened key.
     * @param bool $global - selects prefix. true - global FedEx config, false - RMA FedEx Config.
     *
     * @return string
     */
    public function getConfigData($key, $global = true)
    {
        if ($global) {
            $configData = Mage::getStoreConfig('carriers/fedex/'.$key, $this->getStore());
        } else {
            $configData = Mage::getStoreConfig('rma/fedex/'.$key, $this->getStore());
        }

        return (!$configData) ? '' : $configData;
    }

    /**
     * Returns array of currently allowed FedEx container types
     *
     * @param string $method - current FedEx shipping method.
     * @param Mage_Sales_Model_Order $order - base order for current RMA.
     *
     * @return array
     */
    public function getContainers($method, $order)
    {
        $storeId = ($order) ? $order->getStoreId() : Mage::app()->getStore()->getId();
        $fedexCarrier = $this->getFedexCarrier();
        $countryCode = Mage::getStoreConfig('general/country/default');
        $countryId = Mage::getModel('directory/country')->loadByCode($countryCode)->getId();
        if ($order && $order->getShippingAddress()) {
            $countryId = $order->getShippingAddress()->getCountryId();
        }
        if ($fedexCarrier && $countryId) {
            $params = new Varien_Object(array(
                'method' => $method,
                'country_shipper' => $countryId,
                'country_recipient' => Mage::getStoreConfig(
                    Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                    $storeId
                ),
            ));

            return $fedexCarrier->getContainerTypes($params);
        }

        return array();
    }

    /**
     * Returns array of delivery confirmation types
     *
     * @param Mage_Sales_Core_Order $order - base order for current RMA.
     *
     * @return array
     */
    public function getDeliveryConfirmationTypes($order)
    {
        $storeId = ($order) ? $order->getStoreId() : Mage::app()->getStore()->getId();
        $fedexCarrier = $this->getFedexCarrier();
        $params = new Varien_Object(array('country_recipient' =>
            Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $storeId)));
        if ($fedexCarrier && is_array($fedexCarrier->getDeliveryConfirmationTypes($params))) {
            return $fedexCarrier->getDeliveryConfirmationTypes($params);
        }

        return array();
    }

    /**
     * Creates PDF file from FedEx-generated image file.
     *
     * @param string $content - serialized image content.
     *
     * @return string
     */
    protected function makePDF($content)
    {
        $outputPdf = new Zend_Pdf();
        if (stripos($content, '%PDF-') !== false) {
            $pdfLabel = Zend_Pdf::parse($content);
            foreach ($pdfLabel->pages as $page) {
                $outputPdf->pages[] = clone $page;
            }
        } else {
            $image = imagecreatefromstring($content);
            if (!$image) {
                return false;
            }

            $xSize = imagesx($image);
            $ySize = imagesy($image);
            $page = new Zend_Pdf_Page($xSize, $ySize);

            imageinterlace($image, 0);
            $tmpFileName = sys_get_temp_dir().DS.'shipping_labels_'
                .uniqid(mt_rand()).time().'.png';
            imagepng($image, $tmpFileName);
            $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
            $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);

            unlink($tmpFileName);
            if ($page) {
                $outputPdf->pages[] = $page;
            }
        }

        return $outputPdf;
    }

    /**
     * Creates SOAP Authentification Block, based on FedEx credentials.
     *
     * @return array
     */
    protected function getAuthentificationDetails()
    {
        return array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key' => $this->getConfigData('key'),
                    'Password' => $this->getConfigData('password'),
                ),
            ),
            'ClientDetail' => array(
                'AccountNumber' => $this->getConfigData('account'),
                'MeterNumber' => $this->getConfigData('meter_number'),
            ),
            'TransactionDetail' => array(
                'CustomerTransactionId' => '*** Express Domestic Shipping Request v9 using PHP ***',
            ),
            'Version' => array(
                'ServiceId' => 'ship',
                'Major' => '10',
                'Intermediate' => '0',
                'Minor' => '0',
            ),
        );
    }

    /**
     * Returns product property, that should be used as description
     *
     * @param int $productId
     * @return string
     */
    protected function getProductDescription($productId)
    {
        return Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId,
            $this->getConfigData('fedex_description_attr',
            false), $this->getStore()->getId());
    }

    /**
     * Creates Commodity Block (shipment item list) for SOAP request.
     *
     * @param items $items
     *
     * @return array
     */
    protected function getCommodities($items)
    {
        $commodity = array();
        foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')->load($item['product_id']);
            $commodity[] = array(
                'Name' => $item['name'],
                'NumberOfPieces' => 1,
                'Description' => $this->getProductDescription($item['product_id']),
                'CountryOfManufacture' =>
                    $product->getCountryOfManufacture() ? $product->getCountryOfManufacture() : 'US',
                'Weight' => array(
                    'Units' => 'LB',
                    'Value' => ($item['weight']) ? $item['weight'] : $this->getConfigData('fedex_default_weight',
                        false),
                ),
                'Quantity' => $item['qty'],
                'QuantityUnits' => 'pcs',
                'UnitPrice' => array(
                    'Currency' => $this->getStore()->getCurrentCurrencyCode(),
                    'Amount' => ($product->getPrice()) ? $product->getPrice() : 0,
                ),
                'CustomsValue' => array(
                    'Currency' => $this->getStore()->getCurrentCurrencyCode(),
                    'Amount' => 0,
                ),
            );
        }

        return $commodity;
    }

    /**
     * Validates request
     *
     * @param Mirasvit_Rma_Model_Rma $rma - current RMA
     * @param array $params - array with FedEx label parameters
     * @return array
     */
    public function validateRequest($rma, $params)
    {
        $errors = array();

        $customer = Mage::getModel('customer/customer')->load($rma->getCustomerId());
        $address = Mage::getModel('customer/address')->load($customer->getDefaultBilling());
        if (!$address) {
            $errors[] = Mage::helper('rma')->__('Sender must have billing address!');
        }

        // Settings parameters
        if (!Mage::getStoreConfig('general/store_information/name') ||
            Mage::getStoreConfig('general/store_information/phone') ||
            Mage::getStoreConfig('general/store_information/merchant_country')) {
            $errors[] = Mage::helper('rma')->__('Please, set store credentials at Configuration -> General!');
        }

        $totalWeight = 0;
        foreach ($params['items'] as $item) {
            if (!$item['weight'] && !$this->getConfigData('fedex_default_weight', false)) {
                $errors[] = Mage::helper('rma')->__('Product '.$item['name'].' should have weight');
                continue;
            }
            $totalWeight += $item['weight'];
        }
        if ($totalWeight != $params['params']['weight']) {
            $errors[] = Mage::helper('rma')->__('Please, check products weight: it must be equal to overall!');
        }

        return $errors;
    }

    /**
     * Creates Return Details for SmartPost Parcel Return Service
     * @param Mirasvit_Rma_Model_Rma $rma
     * @return array
     */
    protected function createReturnShipmentDetail($rma)
    {
        $returnDetails = array(
            'SpecialServiceTypes' => 'RETURN_SHIPMENT',
            'ReturnShipmentDetail' => array(
                'ReturnType' => 'PRINT_RETURN_LABEL',
                'Rma' => array(
                    'Number' => $rma->getIncrementId(),
                    'Reason' => 'Return Approved'
                ),
            ),
        );

        return $returnDetails;
    }


    /**
     * Creates SOAP-request and receives FedEx processing data. If success, returns serialized label data.
     * If any error or exception, returns appropriate message.
     *
     * @param Mirasvit_Rma_Model_Rma $rma - current RMA
     * @param array $params - array with FedEx label parameters
     *
     * @return array
     *
     * @throws Zend_Pdf_Exception
     */
    public function createFedexLabel($rma, $params)
    {
        $validateErrors = $this->validateRequest($rma, $params);
        if (!count($validateErrors)) {
            return array('status' => 'fail', 'errata' => $validateErrors);
        }

        $client = $this->getFedexClient();

        $customer = Mage::getModel('customer/customer')->load($rma->getCustomerId());
        $address = $rma->getOrders()->getLastItem()->getShippingAddress();
        if (!$address) {
            if (!$address = $rma->getOrders()->getLastItem()->getBillingAddress()) {
                $address = Mage::getModel('customer/address')->load($customer->getDefaultBilling());
                if (!$address) {
                    return array('status' => 'fail',
                        'errata' => array('Invalid address in order and customer account.' .
                            ' Please, recheck address properties!'));
                }
            }
        }

        // Create customer reference and translate RMA properties
        $customerReference = $this->getConfigData('fedex_reference', false);
        preg_match_all('/\{(.*?)\}/', $customerReference, $matches);
        if (isset($matches[1])) {
            foreach ($matches[1] as $property) {
                $customerReference = str_replace('{' . $property . '}', $rma->getData($property), $customerReference);
            }
        }

        try {
            $streetArr = $address->getStreet();
            $request = array(
                'RequestedShipment' => array(
                    'ShipTimestamp' => time(),
                    'DropoffType' => $this->getConfigData('dropoff'),
                    'PackagingType' => $params['params']['container'],
                    'ServiceType' => $this->getDefaultFedexMethod(),

                    'TotalWeight' => array(
                        'Units' => 'LB',
                        'Value' => $params['params']['weight'],
                    ),

                    // This is customer's credentials
                    'Shipper' => array(
                        'Contact' => array(
                            'PersonName' => trim($address->getFirstname().' '.$address->getLastname()),
                            'CompanyName' => $address->getCompany(),
                            'PhoneNumber' => $address->getTelephone(),
                            'EMailAddress' =>
                                ($customer) ? $customer->getEmail() : $rma->getOrder()->getCustomerEmail(),
                            ),
                            'Address' => array(
                            'StreetLines' => array(
                                $streetArr[0],
                                (isset($streetArr[1]) ? $streetArr[1] : ''),
                            ),
                            'City' => $address->getCity(),
                            'StateOrProvinceCode' =>
                                Mage::getModel('directory/region')->load($address->getRegionId())->getCode(),
                            'PostalCode' => $address->getPostcode(),
                            'CountryCode' => $address->getCountryId(),
                            ),
                    ),

                    // This is our store credentials
                    'Recipient' => array(
                        'Contact' => array(
                            'PersonName' => $this->getConfigData('store_person', false),
                            'CompanyName' => Mage::getStoreConfig('general/store_information/name'),
                            'PhoneNumber' => Mage::getStoreConfig('general/store_information/phone'),
                            'EMailAddress' => Mage::getStoreConfig('trans_email/ident_general/email'),
                            ),
                            'Address' => array(
                            'StreetLines' => array(
                                $this->getConfigData('store_address_line1', false),
                                $this->getConfigData('store_address_line2', false),
                            ),
                            'City' => $this->getConfigData('store_city', false),
                            'StateOrProvinceCode' => $this->getConfigData('store_state_code', false),
                            'PostalCode' => $this->getConfigData('store_postal_code', false),
                            'CountryCode' => Mage::getStoreConfig('general/store_information/merchant_country'),
                            ),
                    ),

                    'ShippingChargesPayment' => array(
                        'PaymentType' => $this->getConfigData('fedex_charges_payor', false),
                        'Payor' => array(
                            'AccountNumber' => $this->getConfigData('account'),
                            'CountryCode' => 'US',
                        ),
                    ),

                    'SpecialServicesRequested' => $this->createReturnShipmentDetail($rma),

                    'CustomsClearanceDetail' => array(
                        'DutiesPayment' => array(
                            'PaymentType' => 'RECIPIENT',
                            'Payor' => array(
                                'AccountNumber' => $this->getConfigData('account'),
                                'CountryCode' => 'US',
                            ),
                        ),
                        'CustomsValue' => array(
                            'Currency' => 'USD',
                            'Amount' => 0,
                        ),

                        // Here goes products details
                        'Commodities' => $this->getCommodities($params['items']),
                    ),

                    'SmartPostDetail' => array(
                        'Indicia' =>
                            ($params['params']['weight'] >= 1) ?
                                $this->getConfigData('fedex_smartpost_indicia', false) :
                                Mirasvit_Rma_Model_Config::FEDEX_SMARTPOST_INDICIA_PRESORTED,
                        'AncillaryEndorsement' => 'RETURN_SERVICE',
                        'HubId' => $this->getConfigData('fedex_smartpost_hubid', false),
                    ),

                    'LabelSpecification' => array(
                        'LabelFormatType' => 'COMMON2D',
                        'ImageType' => 'PNG',
                        'LabelStockType' => 'PAPER_8.5X11_TOP_HALF_LABEL',
                    ),
                    'RateRequestTypes' => array('ACCOUNT'),
                    'PackageCount' => 1,
                    'RequestedPackageLineItems' => array(
                        'SequenceNumber' => '1',
                        'Weight' => array(
                            'Units' => 'LB',
                            'Value' => $params['params']['weight'],
                        ),
                        'CustomerReferences' => array(
                            'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
                            'Value' => $customerReference,
                        ),
                        'SpecialServicesRequested' => array(
                            'SpecialServiceTypes' => 'SIGNATURE_OPTION',
                            'SignatureOptionDetail' => array(
                                'OptionType' => $params['params']['delivery_confirmation'],
                            ),
                        ),
                    ),
                ),
            );

            // Smart Post specials
            if ($this->getDefaultFedexMethod() == strtoupper(Mirasvit_Rma_Model_Config::FEDEX_METHOD_SMART_POST)) {
                unset($request['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']);
                if ($this->getConfigData('fedex_smartpost_indicia', false) !=
                    Mirasvit_Rma_Model_Config::FEDEX_SMARTPOST_INDICIA_PARCEL_RETURN) {
                    unset($request['RequestedShipment']['SpecialServicesRequested']);
                }
            } else {
                unset($request['RequestedShipment']['SmartPostDetail']);
                unset($request['RequestedShipment']['SpecialServicesRequested']);
            }

            $request = $this->getAuthentificationDetails() + $request;
            $response = $client->processShipment($request);

            // @codingStandardsIgnoreStart - SOAP object handling does not fit with PHP Coding Standards
            if ($response->HighestSeverity == 'SUCCESS' || $response->HighestSeverity == 'NOTE') {
                // Let's create label in PDF format
                $pdf = $this->makePDF($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image);
                $label = Mage::getModel('rma/fedex_label');
                $label->setRmaId($rma->getId());
                $label->setLabelDate(time());

                $trackingBlock = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds;
                if(is_array($trackingBlock)) {
                    $trackingBlock = $trackingBlock[0];
                }
                $label->setTrackNumber($trackingBlock->TrackingNumber);

                $label->setPackageNumber(count(Mage::getModel('rma/fedex_label')->getCollection()) + 1);
                $label->setLabelBody($pdf->render());
                $label->save();

                return array('status' => 'success', 'data' => $pdf->render());
            } else {
                $errata = array();
                if (!is_array($response->Notifications)) {
                    $errata[] = $response->Notifications->Severity.' '.$response->Notifications->Code.': '.
                        $response->Notifications->Message;
                } else {
                    foreach ($response->Notifications as $notification) {
                        if (is_array($response->Notifications)) {
                            $errata[] = $notification->Severity.' '.$notification->Code.': '.$notification->Message;
                        } else {
                            $errata[] = $notification;
                        }
                    }
                }

                return array('status' => 'fail', 'errata' => $errata);
            }
            // @codingStandardsIgnoreEnd
        } catch (SoapFault $fault) {
            Mage::log($fault, null, 'fedex-exception.log');

            return array('status' => 'fail',
                'errata' => array('Unexpected exception. Please, review all FedEx settings' .
                    ' and properties of shipping package!'));
        }
    }

    /**
     * If JSON array is created by JavaScript, it will be decoded as stdClass, not as an array.
     * This function decodes it recursively.
     *
     * @param stdClass $jsonData - JSON serialized data
     *
     * @return string
     */
    public function jsonToArray($jsonData)
    {
        $arrayData = get_object_vars($jsonData);
        foreach (array_keys($arrayData) as $key) {
            if (is_object($arrayData[$key])) {
                $arrayData[$key] = $this->jsonToArray($arrayData[$key]);
            }
        }

        return $arrayData;
    }
}
