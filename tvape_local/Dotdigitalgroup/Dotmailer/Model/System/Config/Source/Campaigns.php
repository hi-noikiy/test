<?php

class Dotdigitalgroup_Dotmailer_Model_System_Config_Source_Campaigns
{

    public function toOptionArray()
    {
		$config = Mage::getStoreConfig('dotmailer');
		$ListCampaigns = array();
		$SoapClient = new Zend_Soap_Client("http://apiconnector.com/API.asmx?WSDL",array('soap_version'=>SOAP_1_2));
		$ListCampaigns[] = array('value' => NULL, 'label'=> "Please, select campaign...");
		try {
			$campaigns =  $SoapClient->ListCampaigns(array('username' => $config['dotMailer_group']['dotMailer_api_username'],'password' => $config['dotMailer_group']['dotMailer_api_password']))->ListCampaignsResult->APICampaign;
			if($campaigns)
				if(is_array($campaigns))
					foreach($campaigns as $campaign)
						$ListCampaigns[] = array('value' => $campaign->Id, 'label'=> $campaign->Name);
				else
					$ListCampaigns[] = array('value' => $campaigns->Id, 'label'=> $campaigns->Name);
		} catch (SoapFault $fault) {
		}

		return $ListCampaigns;
    }

}
