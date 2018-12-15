<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Customer extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/customer');
    }

    public function customerMapping($data)
    {
  		if(isset($data['created_by'])){
  			$created_by = $data['created_by'];
  		}else{
  			$helper = Mage::helper('eshopsync/connection');
  			$created_by = $helper::$magento_user;
  		}

      $cusData = Mage::getModel('eshopsync/customer')
                  ->getCollection()
                  ->addFieldToFilter('magento_id',$data['magento_id']);
      if(count($cusData)){
        foreach ($cusData as $model) {
          if($model['entity_id']){
            // $model = Mage::getModel('eshopsync/customer')->load($model['entity_id']);
            $model->setSforceId($data['sforce_id']);
        		$model->setCreatedBy($created_by);
            $model->setErrorHints("");
            $model->setNeedSync("no");
            $model->save();
          }
        }
      }
      else{
    		$model = Mage::getModel('eshopsync/customer');
    		$model->setMagentoId($data['magento_id']);
    		$model->setSforceId($data['sforce_id']);
    		$model->setCreatedBy($created_by);
    		$model->save();
      }
  	}

    public function errorMapping($data)
    {
      $cusData = Mage::getModel('eshopsync/customer')
                  ->getCollection()
                  ->addFieldToFilter('magento_id',$data['magento_id']);
      if(count($cusData)){
        foreach ($cusData as $model) {
          if($model['entity_id']){
            // $model = Mage::getModel('eshopsync/customer')->load($model['entity_id']);
            $model->setErrorHints($data['error_hints']);
            $model->save();
          }
        }
      }
      else{
        $model = Mage::getModel('eshopsync/customer');
        $model->setMagentoId($data['magento_id']);
        $model->setErrorHints($data['error_hints']);
        $model->setNeedSync("yes");
        $model->save();
      }
    }

  	public function updateMapping($mapping_id, $status = 'no')
  	{
  		$model = $this->load($mapping_id);
  		$model->setNeedSync($status);
  		$model->save();
  		return true;
  	}



	public function syncCustomerAsAccount($client, $customer, $action){
		$sforce_id = false;
		$customer_id = $customer->getId();
		$sObject = new stdclass();
		$sObject->Name = $customer->getName();
		$sObject->webkul_es_mage__Magento_Customer_ID__c = $customer_id;
		$sObject->webkul_es_mage__TAX_VAT_Number__c = $customer->getTaxvat();
		if($customer->getDefaultBilling()){
			$billing_address_id = $customer->getDefaultBilling();
			$billing_address = Mage::getModel('customer/address')->load($billing_address_id);
			$sObject->Phone = $billing_address->getTelephone();
			$sObject->Fax = $billing_address->getFax();
			$streets = implode(',', $billing_address->getStreet());
			$sObject->BillingStreet = $streets;
			$sObject->BillingCity = $billing_address->getCity();
			$sObject->BillingPostalCode = $billing_address->getPostcode();
			$sObject->BillingState = $billing_address->getRegion();
			$sObject->BillingCountry = $billing_address->getCountryId();
		}
		if($customer->getDefaultShipping()){
			$shipping_address_id = $customer->getDefaultShipping();
			$shipping_address = Mage::getModel('customer/address')->load($shipping_address_id);
			$streets = implode(',', $shipping_address->getStreet());
			$sObject->ShippingStreet = $streets;
			$sObject->ShippingCity = $shipping_address->getCity();
			$sObject->ShippingPostalCode = $shipping_address->getPostcode();
			$sObject->ShippingState = $shipping_address->getRegion();
			$sObject->ShippingCountry = $shipping_address->getCountryId();
		}
		try
		{
			$createResponse = $client->upsert('webkul_es_mage__Magento_Customer_ID__c', array($sObject), 'Account');

      foreach ($createResponse as $res) {

    			if ($res->success){
    				$sforce_id = $createResponse[0]->id;
    				/*mapping entry if action is export*/
    				if($action == "Export"){
    					$mapping_data = array(
    							'magento_id'=>$customer_id,
    							'sforce_id'=>$sforce_id,
    						);
                //echo '<pre>'; print_r($mapping_data); die;
    					$this->customerMapping($mapping_data);
    				}
    			}elseif($res->errors){
            foreach ($res->errors as $err) {
        				// $message = Mage::helper('eshopsync')->decodeSalesforceLog($err->message);
        				// $error = $action." Error, Customer Id ".$customer_id." Reason >>".$message;
        				Mage::helper('eshopsync')->eshopsyncLog($err->message);

                $mapping_data = array(
                      'magento_id'=>$customer_id,
                      'error_hints'=>$err->message,
                    );
                $this->errorMapping($mapping_data);
                $res = array(
                   'error'  => 1,
                   'val' => $err->message,
                 );
                return $res;
            }
    			}
      }
      $res = array(
         'error'  => 0,
         'val' => $sforce_id,
       );
      return $res;
		}
		catch(Exception $e)
		{
			$error = $action." Error, Customer Id ".$customer_id." >>".$e;
			Mage::log($error, null, 'eshopsync_connector.log');

      $mapping_data = array(
            'magento_id'=>$customer_id,
            'error_hints'=>$e->getMessage(),
          );
      $this->errorMapping($mapping_data);
      $res = array(
         'error'  => 1,
         'val' => $e->getMessage(),
       );
      return $res;
		}
		//return false;
	}

	public function syncSpecificCustomer($client, $customer_id, $action="Export")
	{

		$sforce_id = false;
		$customer = Mage::getModel('customer/customer')->load($customer_id);
		if(!$customer->getName()){
			return false;
		}
		$accDetails = $this->syncCustomerAsAccount($client, $customer, $action);
		if($accDetails['error']){
      $res = array(
        'error'  => 1,
        'val' => $accDetails['val'],
      );
      return $res;
    }
    else{
      $sforce_id = $accDetails['val'];
      foreach ($customer->getAddresses() as $address)	 {
				$address_id = $address->getId();
				$data =  Mage::helper('eshopsync')->fetchMappingDetails('eshopsync/contact', $address_id);
				if($data){
					$mapping_id = $data['entity_id'];
					Mage::getModel('eshopsync/contact')->syncAddressAsContact($client, $customer, $address, $sforce_id, "Update", $mapping_id);
				}else{
					Mage::getModel('eshopsync/contact')->syncAddressAsContact($client, $customer, $address, $sforce_id, "Export");
				}
			}


    }
    $res = array(
      'error'  => 0,
      'val' => $sforce_id,
    );
    return $res;

	}

}
