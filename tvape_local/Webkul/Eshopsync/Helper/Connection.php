<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
define("SOAP_CLIENT_BASEDIR", Mage::getBaseDir('lib')."/Eshopsync/soapclient");
require_once (SOAP_CLIENT_BASEDIR.'/SforceEnterpriseClient.php');
require_once (SOAP_CLIENT_BASEDIR.'/SforceHeaderOptions.php');


class Webkul_Eshopsync_Helper_Connection extends Mage_Core_Helper_Abstract
{
	public static $salesforce_user;
	public static $salesforce_pwd;
	public static $salesforce_token;
	public static $salesforce_client;
	public static $magento_user;

	public function __construct()
	{
		self::$salesforce_user = Mage::getStoreConfig('eshopsync/setting/user');
		self::$salesforce_pwd = Mage::getStoreConfig('eshopsync/setting/pwd');
		self::$salesforce_token = Mage::getStoreConfig('eshopsync/setting/token');
		self::$salesforce_client = Mage::getStoreConfig('eshopsync/setting/client');
		self::$magento_user = $this->getCurrentUser();
	}

	public function getTestConnection($user,$pwd,$token,$clientData)
	{
		self::$salesforce_user = $user;
		self::$salesforce_pwd = $pwd;
		self::$salesforce_token = $token;
		self::$salesforce_client = $clientData;

		$client = false;
		if(self::$salesforce_client == 'enterprise'){
			$wsdl  = SOAP_CLIENT_BASEDIR . '/' . Mage::getStoreConfig('eshopsync/upload/upload_wsdl');
			try{
				$client = new SforceEnterpriseClient();
				$client->createConnection($wsdl);
				$client->login(self::$salesforce_user, self::$salesforce_pwd . self::$salesforce_token);
				$connection = "Congratulation, Magento is successfully connected with Salesforce ".self::$salesforce_client." Api!!!";

			}catch(Exception $e){
				$client = false;
				$connection = "SalesForce Api Connection Failed, ".$e->faultstring;
			}
		}else{
			$connection = "Sorry, Integration For Partner Client is not available. Please Contact us at <a href='mailto:sales@webkul.com'>Webkul</a>.";
		}
		Mage::getSingleton('adminhtml/session')->setConnection(Mage::helper('eshopsync')->__($connection));
		return $client;
	}

	public function getSforceConnection()
	{
		$client = false;
		if(self::$salesforce_client == 'enterprise'){
			$wsdl  = SOAP_CLIENT_BASEDIR . '/' . Mage::getStoreConfig('eshopsync/upload/upload_wsdl');
			try{
				$client = new SforceEnterpriseClient();
				$client->createConnection($wsdl);
				$client->login(self::$salesforce_user, self::$salesforce_pwd . self::$salesforce_token);
				$connection = "Congratulation, Magento is successfully connected with Salesforce ".self::$salesforce_client." Api!!!";

			}catch(Exception $e){
				$client = false;
				$connection = "SalesForce Api Connection Failed, ".$e->faultstring;
			}
		}else{
			$connection = "Sorry, Integration For Partner Client is not available. Please Contact us at <a href='mailto:sales@webkul.com'>Webkul</a>.";
		}
		Mage::getSingleton('adminhtml/session')->setConnection(Mage::helper('eshopsync')->__($connection));
		return $client;
	}

	public function getCurrentUser()
	{
		$username = 'Magento';
		$user = Mage::getSingleton('admin/session')->getUser();
		if($user){
			$username = $username."-".$user->getUsername();
		}else{
			$username = $username.'-Front';
		}
		return $username;
	}

}
