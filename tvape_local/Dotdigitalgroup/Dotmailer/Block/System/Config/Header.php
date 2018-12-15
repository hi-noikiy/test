<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . "/config/dotMailerConfig.php";
class Dotdigitalgroup_Dotmailer_Block_System_Config_Header extends Mage_Adminhtml_Block_Abstract
{
    protected $_template = 'dotdigitalgroup/dotmailer/system/config/header.phtml';
    public $trialAccountLink;
    public $trialAccountText;
    public $supportLink;
    public $supportText;
    public $phoneNumber;

    public function __construct()
    {
		$this->SoapClient       = new Zend_Soap_Client("http://apiconnector.com/API.asmx?WSDL");
		$this->config           = Mage::getStoreConfig('dotmailer');
		$dotMailerConfig        = new dotMailerConfig();
		$this->dotMailerConfig  = $dotMailerConfig;
		$this->bannerUrl        = $dotMailerConfig->getBannerUrl();
		$this->trialAccountLink = $dotMailerConfig->getTrialAccountLink();
		$this->trialAccountText = $dotMailerConfig->getTrialAccountText();
		$this->supportLink      = $dotMailerConfig->getSupportLink();
		$this->supportText      = $dotMailerConfig->getSupportText();
		$this->phoneNumber      = $dotMailerConfig->getPhoneNumber();
    }

    public function checkApi()
    {

		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password = $this->config['dotMailer_group']['dotMailer_api_password'];
		$result = true;
		$error = NULL;

		if($username && $password)
		{
			try {
				$dotm_fields = $this->SoapClient->ListContactDataLabels(array('username' => $username,'password' => $password))->ListContactDataLabelsResult->ContactDataLabel;
				$fields = array(
							'date_stamp'=>'date_stamp',
							'created_at'=>'created_at',
							'customer_id'=>'customer_id',
							'firstname'=>'firstname',
							'lastname'=>'lastname',
							'country_id'=>'country_id',
							'region_id'=>'region_id',
							'region'=>'region',
							'city'=>'city',
							'street'=>'street',
							'telephone'=>'telephone',
							'postcode'=>'postcode',
							'store_id'=>'store_id',
							'website_id'=>'website_id'
						);

				foreach($dotm_fields as $field)
					if(in_array(strtolower($field->Name),$fields))
						unset($fields[strtolower($field->Name)]);

				foreach($fields as $fieldname)
					try {
						$this->SoapClient->CreateDataField( array("username" => $username, "password" => $password, "fieldname" => $fieldname, "datatype" => "String") );
					} catch (SoapFault $fault) {
					}

			} catch (SoapFault $fault) {
				$result = false;
				$error = $fault->getMessage();//"Wrong API Credentials";
			}
		}
		else
			$result = false;

		return array('result' => $result, 'error' => $error);
    }

	public function enableForOrders()
	{
		return $this->dotMailerConfig->getEnableForOrders();
	}

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}
