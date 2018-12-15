<?php
class dotMailerConfig
{	protected $brandName        = "dotMailer";
	protected $bannerUrl        = "http://www.dotmailer.co.uk";
	protected $supportLink      = "http://www.dotmailer.co.uk/support/";
	protected $supportText      = "dotMailer Support";
	protected $trialAccountLink = "https://www.dotmailer.co.uk/trial_account.aspx";
	protected $trialAccountText = "Get a free dotMailer account now";
	protected $logoLink         = "http://www.dotmailer.co.uk/images/dm_logo.gif";
	protected $phoneNumber      = "0845 337 9171";
	protected $enableForOrders  = true;

	public function __construct()
	{		if( file_exists(dirname(__FILE__).'/config.xml') ) {			$config = simplexml_load_file(dirname(__FILE__).'/config.xml');
			if( is_object($config) ) {
				if( isset($config->brand_name) ) {
					$this->brandName = $config->brand_name;
				}
				if( isset($config->banner_url) ) {
					$this->bannerUrl = $config->banner_url;
				}
				if( isset($config->support_link) ) {
					$this->supportLink = $config->support_link;
				}
				if( isset($config->support_text) ) {					$this->supportText = $config->support_text;				}
				if( isset($config->trial_account_link) ) {
					$this->trialAccountLink = $config->trial_account_link;
				}
				if( isset($config->trial_account_text) ) {
					$this->trialAccountText = $config->trial_account_text;
				}
				if( isset($config->logo_link) ) {
					$this->logoLink = $config->logo_link;
				}
				if( isset($config->phone_number) ) {
					$this->phoneNumber = $config->phone_number;
				}

				if( isset($config->enable_for_orders) ) {
					if($config->enable_for_orders == "1") {						$this->enableForOrders = true;					} else {						$this->enableForOrders = false;					}
				}
			}		}
	}
	public function getBrandName()
	{		return $this->brandName;	}
	public function getBannerUrl()
	{
		return $this->bannerUrl;
	}
	public function getSupportLink()
	{
		return $this->supportLink;
	}
	public function getSupportText()
	{
		return $this->supportText;
	}
	public function getTrialAccountLink()
	{
		return $this->trialAccountLink;
	}
	public function getTrialAccountText()
	{
		return $this->trialAccountText;
	}
	public function getLogoLink()
	{
		return $this->logoLink;
	}	public function getPhoneNumber()
	{
		return $this->phoneNumber;
	}
	public function getEnableForOrders()
	{
		return $this->enableForOrders;
	}
}