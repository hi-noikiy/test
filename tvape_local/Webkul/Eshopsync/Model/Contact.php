<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Contact extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/contact');
    }

    public function contactMapping($data)
    {
		if(isset($data['created_by'])){
			$created_by = $data['created_by'];
		}else{
			$helper = Mage::helper('eshopsync/connection');
			$created_by = $helper::$magento_user;
		}
		$model = Mage::getModel('eshopsync/contact');
		$model->setCustomerId($data['customer_id']);
		$model->setMagentoId($data['magento_id']);
		$model->setSforceId($data['sforce_id']);
		$model->setCreatedBy($created_by);
		$model->save();
	}

	public function syncAddressAsContact($client, $customer, $address, $account_id, $action='Export', $mapping_id=false)
	{
		$sforce_id = false;
		$address_id = $address->getId();
		$customer_id = $customer->getId();
		$sObject = new stdclass();
		$sObject->FirstName = $address->getFirstname();
		$sObject->LastName = $address->getLastname();
		$sObject->AccountId = $account_id;
		$sObject->Email = $customer->getEmail();
		$streets = implode(',', $address->getStreet());
		$sObject->MailingStreet = $streets;
		$sObject->MailingCity = $address->getCity();
		$sObject->MailingPostalCode = $address->getPostcode();
		$sObject->Phone = $address->getTelephone();
		$sObject->MailingCountry = $address->getCountryId();
		$sObject->MailingState = $address->getRegion();

    $sObject->webkul_es_mage__Magento_Contact_ID__c = $address_id;
		try
		{
			if($action == "Export"){
				$createResponse = $client->create(array($sObject), 'Contact');
				if ($createResponse[0]->success){
					$sforce_id = $createResponse[0]->id;
					/*mapping entry if action is export*/
					if($action == "Export"){
						$mapping_data = array(
								'customer_id'=>$customer_id,
								'magento_id'=>$address_id,
								'sforce_id'=>$sforce_id,
							);
						$this->contactMapping($mapping_data);
					}
				}elseif(isset($createResponse[0]->error)){
					$message = Mage::helper('eshopsync')->decodeSalesforceLog($createResponse[0]->error);
					$error = $action." Error, Address Id ".$address_id." Reason >>".$message;
					Mage::helper('eshopsync')->eshopsyncLog($error);
				}
			}elseif($action == 'Update'){
				$sObject->Id = $mapping_id;
				$upsertResponse = $client->upsert('webkul_es_mage__Magento_Contact_ID__c', array($sObject), 'Contact');

				if ($upsertResponse[0]->success){
					$sforce_id = $upsertResponse[0]->id;
				}elseif(isset($upsertResponse[0]->error)){
					$message = Mage::helper('eshopsync')->decodeSalesforceLog($upsertResponse[0]->error);
					$error = $action." Error, Address Id ".$address_id." Reason >>".$message;
					Mage::helper('eshopsync')->eshopsyncLog($error);
				}
			}
			return $sforce_id;
		}
		catch(Exception $e)
		{
			$error = $action." Error, Customer Id ".$customer_id." >>".$e;
			Mage::log($error, null, 'eshopsync_connector.log');
		}
		return false;
	}

  public function syncAddressAsContactGuest($client, $address, $account_id, $action='Export', $order_id)
	{
		$sforce_id = false;
		$address_id = $address->getEntityId();
		// $customer_id = $customer->getId();
		$sObject = new stdclass();
		$sObject->FirstName = $address->getFirstname();
		$sObject->LastName = $address->getLastname();
		$sObject->AccountId = $account_id;
		// $sObject->Email = $customer->getEmail();
    $sObject->Email = $address->getEmail();
		$streets = implode(',', $address->getStreet());
		$sObject->MailingStreet = $streets;
		$sObject->MailingCity = $address->getCity();
		$sObject->MailingPostalCode = $address->getPostcode();
		$sObject->Phone = $address->getTelephone();
		$sObject->MailingCountry = $address->getCountryId();
		$sObject->MailingState = $address->getRegion();
    $sObject->webkul_es_mage__Magento_Order_ID__c = $order_id;
    $sObject->webkul_es_mage__Magento_Contact_ID__c = $address_id;
		try
		{
			if($action == "Export"){
				$createResponse = $client->create(array($sObject), 'Contact');
				if ($createResponse[0]->success){
					$sforce_id = $createResponse[0]->id;
				}

			}
			return $sforce_id;
		}
		catch(Exception $e)
		{
			// $error = $action." Error, Customer Id ".$customer_id." >>".$e;
			Mage::log($error, null, 'eshopsync_connector.log');
		}
		return false;
	}

  public function syncOtherAddressAsContactGuest($client, $address, $action='Update', $address_id)
	{
		$sObject = new stdclass();
		$streets = implode(',', $address->getStreet());
		$sObject->OtherStreet = $streets;
		$sObject->OtherCity = $address->getCity();
		$sObject->OtherPostalCode = $address->getPostcode();
		$sObject->OtherPhone = $address->getTelephone();
		$sObject->OtherCountry = $address->getCountryId();
		$sObject->OtherState = $address->getRegion();
    $sObject->webkul_es_mage__Magento_Contact_ID__c = $address_id;
		try
		{
			if($action == "Update"){
				$client->upsert('webkul_es_mage__Magento_Contact_ID__c', array($sObject), 'Contact');
				if ($createResponse[0]->success){
					$sforce_id = $createResponse[0]->id;
				}

			}
			return $sforce_id;
		}
		catch(Exception $e)
		{
			// $error = $action." Error, Customer Id ".$customer_id." >>".$e;
			Mage::log($error, null, 'eshopsync_connector.log');
		}
		return false;
	}

}
