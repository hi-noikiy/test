<?php

namespace ShippyPro\ShippyPro\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;

class ShippyProCarrier extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'shippypro';
    protected $_helper;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
	protected $_logger;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Psr\Log\LoggerInterface $logger,
        \ShippyPro\ShippyPro\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        $this->_rateResultFactory = $rateResultFactory;
		$this->_rateMethodFactory = $rateMethodFactory;
		$this->_logger = $logger;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger);
    }

    public function getAllowedMethods()
    {
        return ['ShippyPro' => 'ShippyPro'];
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active') || empty($request->getDestCity())) {
            return false;
        }

        $currencyCode = $this->_helper->getStoreManager()->getStore()->getCurrentCurrencyCode();

        $originStreet = $this->_helper->getScopeConfig()->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ADDRESS1, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_helper->getStoreId());
        $originCity = $this->_helper->getScopeConfig()->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_helper->getStoreId());
        $originState = $this->_helper->getScopeConfig()->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_helper->getStoreId());
        $originPostcode = $this->_helper->getScopeConfig()->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_helper->getStoreId());
		$originCountry = $this->_helper->getScopeConfig()->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_helper->getStoreId());
		
        $displayDropoffLocations = $this->_helper->getScopeConfig()->getValue('carriers/shippypro/displayDropoffLocations', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->_helper->getStoreId());

        $weight = 0;
        foreach ($request->getAllItems() as $item) {
            $weight += ($item->getWeight() * $item->getQty()) ;        
        }

        $grandTotal = $request->getBaseSubtotalInclTax();
        $destCountry = $request->getDestCountryId();

        $request = array(
			"Method" => "GetRates",
			"Params" => array(
				"to_address" => array(
					"name" => "John Doe",
					"company" => "",
					"street1" => $request->getDestStreet()[0],
					"street2" => "",
					"city" => $request->getDestCity(),
					"state" => $request->getDestState(),
					"zip" => $request->getDestPostcode(),
					"country" => $destCountry,
					"phone" => "5551231234",
					"email" => ""
				) ,
				"from_address" => array(
					"name" => "John Doe",
					"company" => "John Doe",
					"street1" => $originStreet,
					"street2" => "",
					"city" => $originCity,
					"state" => $originState,
					"zip" => $originPostcode,
					"country" => $originCountry,
					"phone" => "+3903021341",
					"email" => ""
				) ,
				"parcels" => array(
					array(
						"length" => 1,
						"width" => 1,
						"height" => 1,
						"weight" => $weight
					)
				),
				"CashOnDelivery" => 0,
				"CashOnDeliveryCurrency" => $currencyCode,
				"Insurance" => 0,
				"InsuranceCurrency" => $currencyCode,
				"ContentDescription" => "SAMPLE",
				"TotalValue" => $grandTotal
			)
		);

		$rates = $this->_helper->apiRequest($request);

		$installedUPSAccessPointCarrier = false;
		$installedSDADeliveryPointsCarriers = false;
        
		$result = $this->_rateResultFactory->create();
		
		if (isset($rates->Rates))
		{
			foreach ($rates->Rates as $tarif)
			{
				$result->append($this->_getStandardShippingRate($tarif, $grandTotal, $destCountry));
	
				// UPS Access Points
				if ($tarif->carrier == "UPS" && !$installedUPSAccessPointCarrier && $displayDropoffLocations) {
	
					$tarif->service = "AccessPoint";
	
					$result->append($this->_getStandardShippingRate($tarif, $grandTotal, $destCountry));
	
					$installedUPSAccessPointCarrier = true;
				}

				// SDA Delivery Points
				if (($tarif->carrier == "SDA" || $tarif->carrier == "POSTEITALIANE") && !$installedSDADeliveryPointsCarriers && $displayDropoffLocations) {
					
					$sdaDeliveryPointsTypes = [
						"Punto Poste",
						"Punto Poste Locker",
						"Casella Postale",
						"Fermo Posta"
					];			
		
					foreach ($sdaDeliveryPointsTypes as $sdaDeliveryPointsType)
					{						
						$tarif->service = $sdaDeliveryPointsType;
		
						$result->append($this->_getStandardShippingRate($tarif, $grandTotal, $destCountry));
					}					
	
					$installedSDADeliveryPointsCarriers = true;
				}
			}
		}
		else {
			$this->_logger->debug('Error getting ShippyPro rates: ' . print_r($rates, true)); 
		}

		return $result;
    }

    protected function _getStandardShippingRate($tarif, $grandTotal, $destCountry)
	{
		$freeShipping = $this->_helper->getScopeConfig()->getValue('carriers/shippypro/offerfreeshipping', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->_helper->getStoreId());
		$markupType = $this->_helper->getScopeConfig()->getValue('carriers/shippypro/markupType', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->_helper->getStoreId());
        $markup = $this->_helper->getScopeConfig()->getValue('carriers/shippypro/markup', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $this->_helper->getStoreId());

        $rate = $this->_rateMethodFactory->create();
		$rate->setCarrier($this->_code);
		$rate->setCarrierTitle($tarif->carrier);
		$rate->setMethod($tarif->carrier . "_" . $tarif->service);

		if ($tarif->delivery_days)
			$title = $tarif->carrier . ' ' . $tarif->service . ' (' . $tarif->delivery_days . ' days)';
		else
			$title = $tarif->carrier . ' ' . $tarif->service;

		$rate->setMethodTitle($title);

		if (is_numeric($freeShipping) && $freeShipping != 0 && $grandTotal > $freeShipping)
		{
			$rate->setPrice(0);
			$rate->setCost(0);
		}
		else
		{
			if ($markupType == 1) {
				$ricarico = 1 + ($markup / 100);
				$rate->setPrice(round($tarif->rate * $ricarico, 2));
			} else {
				$rate->setPrice($tarif->rate + $markup);
			}

			$rate->setCost($tarif->rate);
		}

		return $rate;
	}
}
