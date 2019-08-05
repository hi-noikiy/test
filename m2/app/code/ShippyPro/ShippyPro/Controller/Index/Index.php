<?php
 
namespace ShippyPro\ShippyPro\Controller\Index;

use Magento\Framework\App\Action\Context;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_helper;
 
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \ShippyPro\ShippyPro\Helper\Data $helper)
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;

        parent::__construct($context);
    }
 
    public function execute()
    {
        $carrier = !empty($this->getRequest()->getParam("carrier")) ? $this->getRequest()->getParam("carrier") : "UPS_AccessPoint";

        $carrierType = explode("_", $carrier)[0];
        $carrierService = explode("_", $carrier)[1];

        $city = $this->getRequest()->getParam("city");
        $country = $this->getRequest()->getParam("country");
        $zip = $this->getRequest()->getParam("zip");
        $max_distance = $this->getRequest()->getParam("max_distance");
        
        if ($carrierType == "UPS")
        {            
            $request = array(
                "Method" => "GetUPSAccessPoints",
                "Params" => 
                array(
                    "city" => $city,
                    "country" => $country,
                    "zip" => $zip,
                    "max_distance" => $max_distance,
                )
            );
        }
        else if ($carrierType == "SDA" || $carrierType == "POSTEITALIANE")
        {
            $deliveryPointsTypeCode = "";
            
            if ($carrierService == "Punto Poste") $deliveryPointsTypeCode = "RTZ";
            if ($carrierService == "Punto Poste Locker") $deliveryPointsTypeCode = "APT";
            if ($carrierService == "Casella Postale") $deliveryPointsTypeCode = "CPT";
            if ($carrierService == "Fermo Posta") $deliveryPointsTypeCode = "FMP"; 

            $request = array(
                "Method" => "GetSDADeliveryPoints",
                "Params" => 
                array(
                    "zip" => $zip,
                    "deliveryPointsTypeCode" => $deliveryPointsTypeCode
                )
            );
        }

        $response = $this->_helper->apiRequest($request);

        $accessPoints = array();

        if (isset($response->AccessPoints))
        {	
            foreach ($response->AccessPoints as $accessPoint)
            {
                $accessPoints[] = array(
                    "Latitude" => $accessPoint->Geocode->Latitude,
                    "Longitude" => $accessPoint->Geocode->Longitude,
                    "AccessPointID" => $accessPoint->AccessPointInformation->PublicAccessPointID,
                    "Description" => $accessPoint->LocationAttribute->OptionCode->Description,
                    "Distance" => $accessPoint->Distance->Value . " " . $accessPoint->Distance->UnitOfMeasurement->Code,
                    "Hours" => $accessPoint->StandardHoursOfOperation,
                    "Name" => $accessPoint->AddressKeyFormat->ConsigneeName,
                    "Address" => $accessPoint->AddressKeyFormat->AddressLine,
                    "City" => $accessPoint->AddressKeyFormat->PoliticalDivision2,
                    "Zip" => $accessPoint->AddressKeyFormat->PostcodePrimaryLow
                );
            }	
        }

        if (isset($response->DeliveryPoints))
        {	
            foreach ($response->DeliveryPoints as $deliveryPoint)
            {
                $accessPoints[] = array(
                    "Latitude" => trim($deliveryPoint->ygradi),
                    "Longitude" => trim($deliveryPoint->xgradi),
                    "AccessPointID" => trim($deliveryPoint->codiceUfficio),
                    "Description" => trim($deliveryPoint->descrizioneUfficio),
                    "Distance" => "",
                    "Hours" => trim($deliveryPoint->oraApLun . " " . $deliveryPoint->oraChLun),
                    "Name" => trim($deliveryPoint->descrizioneUfficio),
                    "Address" => trim($deliveryPoint->indirizzo),
                    "City" => trim($deliveryPoint->localita),
                    "Zip" => trim($deliveryPoint->cap)
                );
            }	
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($accessPoints)
        );
    }
}