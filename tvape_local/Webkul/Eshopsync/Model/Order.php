<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Order extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/order');
    }

 	public function orderMapping($data)
  {
		if(isset($data['created_by'])){
			$created_by = $data['created_by'];
		}else{
			$helper = Mage::helper('eshopsync/connection');
			$created_by = $helper::$magento_user;
		}

    $orderData = Mage::getModel('eshopsync/order')
                ->getCollection()
                ->addFieldToFilter('magento_id',$data['magento_id']);
    if(count($orderData)){
      foreach ($orderData as $model) {
        if($model['entity_id']){
          // $model = Mage::getModel('eshopsync/order')->load($model['entity_id']);
          $model->setSforceId($data['sforce_id']);
          $model->setCreatedBy($created_by);
          $model->setErrorHints("");
          $model->setNeedSync("no");
          $model->setAccountId($data['account_id']);
          $model->save();
        }
      }
    }
    else{
      $model = Mage::getModel('eshopsync/order');
      $model->setMagentoId($data['magento_id']);
      $model->setSforceId($data['sforce_id']);
      $model->setCreatedBy($created_by);
      $model->setAccountId($data['account_id']);
      $model->save();
    }
	}

  public function errorMapping($data)
  {
    $orderData = Mage::getModel('eshopsync/order')
                ->getCollection()
                ->addFieldToFilter('magento_id',$data['magento_id']);
    if(count($orderData)){
      foreach ($orderData as $model) {
        if($model['entity_id']){
          // $model = Mage::getModel('eshopsync/order')->load($model['entity_id']);
          $model->setErrorHints($data['error_hints']);
          $model->save();
        }
      }
    }
    else{
      $model = Mage::getModel('eshopsync/order');
      $model->setMagentoId($data['magento_id']);
      $model->setErrorHints($data['error_hints']);
      $model->setNeedSync("yes");
      $model->save();
    }
  }

    public function exportSpecificOrder($client, $order_id){
    	$This_order = Mage::getModel('sales/order')->load($order_id);
      if($This_order['customer_is_guest'] && !Mage::getStoreConfig('eshopsync/guest/enable')){
        return false;
      }

    	$increment_id = $This_order->getIncrementId();
      if($This_order['customer_is_guest']){
        $account_id = Mage::getStoreConfig('eshopsync/guest/account_id');

        $billing_address = $This_order->getBillingAddress();
        $shipping_address = $This_order->getShippingAddress();
        Mage::getModel('eshopsync/contact')->syncAddressAsContactGuest($client, $billing_address, $account_id, "Export", $increment_id);

        $name1 = $billing_address->getFirstname().' '.$billing_address->getLastname();
        $name2 = $shipping_address->getFirstname().' '.$shipping_address->getLastname();
        $street1 = $billing_address->getStreet();
        $street2 = $shipping_address->getStreet();

        if($name1 == $name2){
          if($street1 == $street2){
            // do nothing
          }
          else{
            Mage::getModel('eshopsync/contact')->syncOtherAddressAsContactGuest($client, $shipping_address, "Update",  $billing_address->getEntityId());
          }
        }
        else{
          Mage::getModel('eshopsync/contact')->syncAddressAsContactGuest($client, $shipping_address, $account_id, "Export", $increment_id);
        }





      }
      else{
    	  $account_id = $this->getSalesforceAccountId($client, $This_order);
      }
    	if($account_id){
    		$sforce_order_id = $this->createSalesforceOrder($client, $This_order, $account_id);

    		if(!$sforce_order_id['error']){
    			$this->createSalesforceOrderLine($client, $This_order, $sforce_order_id['val']);
    			$mapping_data = array(
					'magento_id'=>$order_id,
					'account_id'=>$account_id,
					'sforce_id'=>$sforce_order_id['val'],
				    );
    			$this->orderMapping($mapping_data);
    			return true;
    		}else{
          $mapping_data = array(
					'magento_id'=>$order_id,
          'error_hints'=>$sforce_order_id['val']
				    );
          $this->errorMapping($mapping_data);
    			return false;
    		}
    	}else{
    		$error = "Order Export Error, Order Id ".$increment_id." Reason >> Customer Account not found!!!";
  			Mage::helper('eshopsync')->eshopsyncLog($error);
        $mapping_data = array(
        'magento_id'=>$order_id,
        'error_hints'=>"Customer Account not found"
          );
        $this->errorMapping($mapping_data);
    		return false;
    	}
    }

    public function createSalesforceOrder($client, $This_order, $account_id)
    {
    	$increment_id = $This_order->getIncrementId();
      $order_id = $This_order->getEntityId();
    	$orderRecords = new stdclass();
    	$orderRecords->AccountId = $account_id;
  		$orderRecords->Status = 'draft';
  		$orderRecords->EffectiveDate = $This_order->getCreatedAt();

  		/*setting default pricebook*/
  		$orderRecords->Pricebook2Id = Mage::getStoreConfig('eshopsync/default/pricebook');

  		/*billing information*/
  		$billing_address = $This_order->getBillingAddress();

  		$orderRecords->BillingCity = $billing_address->getCity();
  		$orderRecords->BillingCountry = $billing_address->getCountryId();
  		$orderRecords->BillingPostalCode = $billing_address->getPostcode();
  		$orderRecords->BillingState = $billing_address->getRegion();
  		$streets = implode(',', $billing_address->getStreet());
  		$orderRecords->BillingStreet = $streets;

		/*shipping information*/
  		$shipping_address = $This_order->getShippingAddress();
  		if($shipping_address){
  			$orderRecords->ShippingCity = $shipping_address->getCity();
  			$orderRecords->ShippingCountry = $shipping_address->getCountryId();
  			$orderRecords->ShippingPostalCode = $shipping_address->getPostcode();
  			$orderRecords->ShippingState = $shipping_address->getRegion();
  			$streets = implode(',', $shipping_address->getStreet());
  			$orderRecords->ShippingStreet =	$streets;
  		}

  		$orderRecords->webkul_es_mage__Mage_Order_Number__c = $increment_id;
  		$orderRecords->webkul_es_mage__Magento_Order_Status__c = $This_order->getStatus();
  		$orderRecords->webkul_es_mage__Magento_Shipment_Method__c = $This_order->getShippingDescription();
  		$orderRecords->webkul_es_mage__Magento_Payment_Method__c = $This_order->getPayment()->getMethodInstance()->getTitle();
      //echo '<pre>'; print_r($orderRecords); die;
  		try
  		{
  			$orderUpserted = $client->upsert('webkul_es_mage__Mage_Order_Number__c', array($orderRecords), 'Order');
  			if ($orderUpserted[0]->success){
          $res = array(
             'error'  => 0,
             'val' => $orderUpserted[0]->id,
           );
          return $res;
  				//return $orderUpserted[0]->id;
        }
  			elseif(isset($orderUpserted[0]->errors)){
  				$message = Mage::helper('eshopsync')->decodeSalesforceLog($orderUpserted[0]->errors);
  				$error = "Order Export Error, Order Id ".$increment_id." Reason >>".$message;
  				Mage::helper('eshopsync')->eshopsyncLog($error);

          $mapping_data = array(
                'magento_id'=>$order_id,
                'error_hints'=>$error,
              );
          $this->errorMapping($mapping_data);
          $res = array(
             'error'  => 1,
             'val' => $error,
           );
          return $res;
  			}
  		}
  		catch(Exception $e)
  		{
  			$error = "Order Export Error, Order Id ".$increment_id." Reason >>".$e;
  			Mage::helper('eshopsync')->eshopsyncLog($error);

        $mapping_data = array(
              'magento_id'=>$order_id,
              'error_hints'=>$e->getMessage(),
            );
        $this->errorMapping($mapping_data);
        $res = array(
           'error'  => 1,
           'val' => $e->getMessage(),
         );
        return $res;
  		}
  		return false;
    }

    public function createSalesforceOrderLine($client, $This_order, $sforce_order_id)
    {

    	$products_array = array();
    	$increment_id = $This_order->getIncrementId();
    	$items = $This_order->getAllItems();

    	foreach($items as $item){

    		$sforce_product_id = 0;
    		$product_id = $item->getProductId();

    		/*check for other types of products*/
    		$BasePrice = $item->getPriceInclTax();
    		$item_type = $item->getProductType();
        $associated = array();

        $parentConf = Mage::getModel('catalog/product')->load($product_id);

        if($item_type == 'configurable'){
  				// continue;
          $childArray = array();
          $sforceArray = array();
          foreach($item->getChildrenItems() as $child){
            $sforceArray[] = Mage::getModel('eshopsync/product')->syncSpecificProduct($client, $child->getProductId());
            $childId = $child->getProductId();
            $childArray[] = $childId;
            $proChild = Mage::getModel('catalog/product')->load($childId)->getData();
            // $parent = Mage::getModel('catalog/product')->load($product_id);
            $optionsData = $parentConf->getTypeInstance(true)->getConfigurableAttributesAsArray($parentConf);
            // echo '<pre>'; print_r($optionsData); die;
            foreach ($proChild as $key => $value) {
              foreach ($optionsData as $optValue) {
                if($optValue['attribute_code'] == $key){
                  foreach ($optValue['values'] as $oValue) {
                    if($oValue['value_index'] == $value){
                      $attribute[$optValue['frontend_label']] = $oValue['label'];
                    }
                  }
                }
              }
            }
          }
  			}

			if($item_type == 'bundle'){
            $options = $item->getProductOptions();
            $optionIds = array_keys($options['info_buyRequest']['bundle_option']);
            $types = Mage_Catalog_Model_Product_Type::getTypes();
            $typemodel = Mage::getSingleton($types[Mage_Catalog_Model_Product_Type::TYPE_BUNDLE]['model']);
            $typemodel->setConfig($types[Mage_Catalog_Model_Product_Type::TYPE_BUNDLE]);
            $selections = $typemodel->getSelectionsCollection($optionIds, $item);
            $selection_map = array();
            foreach($selections->getData() as $selection) {
                if(!isset($selection_map[$selection['option_id']])) {
                    $selection_map[$selection['option_id']] = array();
                }
                $selection_map[$selection['option_id']][$selection['selection_id']] = $selection;
            }
            $chosen_ids = array();
            foreach($options['info_buyRequest']['bundle_option'] as $op => $sel) {

              $ent_id = $selection_map[$op][$sel]['entity_id'];
              $chosen_ids[] = $ent_id;
              $sforceBundleArray[] = Mage::getModel('eshopsync/product')->syncSpecificProduct($client,$ent_id);
              $name = Mage::getModel('catalog/product')->load($ent_id)->getName();
              $qty = $selection_map[$op][$sel]['selection_qty'];
              $bundleStr .= 'Product:'.$name.';Quantity:'.$qty.';';
            }

            $p = Mage::getModel('catalog/product')->load($product_id);
    				$price_type = $p->getPriceType();
    				if(!$price_type)
    					$BasePrice = 0;
			}

			if($item->getParentItemId() != Null){
				$parent_id = $item->getParentItemId();
				$parent = Mage::getModel('sales/order_item')->load($parent_id);
				if($parent->getProductType() == 'configurable'){
					$BasePrice = $parent->getPriceInclTax();
				}
			}
			/* Fetching Salesforce Product id */
    		$data =  Mage::helper('eshopsync')->fetchMappingDetails('eshopsync/product', $product_id);
        // echo '<pre>'; print_r($data); die;

        if(!in_array($product_id,$childArray) && !in_array($product_id,$chosen_ids)){
      		if($data){
      			$sforce_product_id = $data['sforce_id'];
      		}else{
      			$sforce_product_id_array = Mage::getModel('eshopsync/product')->syncSpecificProduct($client, $product_id);
            // echo 'sforce_product_id_array<pre>'; print_r($sforce_product_id_array);
            $sforce_product_id = $sforce_product_id_array['val'];
      		}
      		if($sforce_product_id){
      			try
  				{
  					$pricebook = Mage::getStoreConfig('eshopsync/default/pricebook'); //echo "pricebook= ".$pricebook;
  					$pricebookResult = $client->query("SELECT Id FROM PricebookEntry WHERE Product2Id = '".$sforce_product_id."' and Pricebook2Id = '".$pricebook."'");
            // echo 'pricebookResult= <pre>'; print_r($pricebookResult); die;
  					if ($pricebookResult->done) {
  						$orderItems = new stdclass();
  						$orderItems->Quantity = $item->getQtyOrdered();
  						$orderItems->OrderId = $sforce_order_id;
  						$orderItems->UnitPrice = $BasePrice;

              if($item_type == 'configurable'){
                $confProds = "";
                foreach ($attribute as $attrName => $valueName) {
                  $confProds .= $attrName.':'.$valueName.';';
                }
                $orderItems->Description = $confProds;
              }
              // elseif ($item_type == 'bundle') {
              //   $orderItems->Description = $bundleStr;
              // }
              else{
                $orderItems->Description = $item->getName();
              }
  						$orderItems->PricebookEntryId = $pricebookResult->records[0]->Id;

              if ($item_type == 'bundle') {
                $orderItems->webkul_es_mage__Grouped_Product_Description__c = $bundleStr;
              }
              else{
                $orderItems->webkul_es_mage__Grouped_Product_Description__c = "";
              }

  						$products_array[] = $orderItems;
  					}
            // echo '<pre>'; print_r($products_array); die;
  				}
  				catch(Exception $e)
  				{}
      		}
        }
    	}
    	try
		{
      // echo 'products_array= <pre>'; print_r($products_array); die;
			$orderUpdated = $client->create($products_array, 'OrderItem');
			if ($orderUpdated[0]->success)
				return true;
			elseif(isset($orderUpdated[0]->errors)){
				$message = Mage::helper('eshopsync')->decodeSalesforceLog($orderUpdated[0]->errors);
				$error = "Order Line Export Error, Order Id ".$increment_id." Reason >>".$message;
				Mage::helper('eshopsync')->eshopsyncLog($error);
			}
		}
		catch(Exception $e)
		{
			$error = "Order Lines Export Error, Order Id ".$increment_id." Reason >>".$e;
			Mage::helper('eshopsync')->eshopsyncLog($error);
		}
		return false;

    }

    public function getSalesforceAccountId($client, $This_order)
    {
		$account_id = false;
		$billing_address = $This_order->getBillingAddress();
		if($This_order->getCustomerIsGuest() == 1){
			$sObject = new stdclass();
			$sObject->Name = $billing_address->getName();
			$sObject->webkul_es_mage__TAX_VAT_Number__c = $billing_address->getVatId();
			$sObject->Phone = $billing_address->getTelephone();
			$sObject->Fax = $billing_address->getFax();
			$streets = implode(',', $billing_address->getStreet());
			$sObject->BillingStreet = $streets;
			$sObject->BillingCity = $billing_address->getCity();
			$sObject->BillingPostalCode = $billing_address->getPostcode();
			$sObject->BillingState = $billing_address->getRegion();
			$sObject->BillingCountry = $billing_address->getCountryId();
			try{
				$createResponse = $client->create(array($sObject), 'Account');
				if ($createResponse[0]->success){
					$account_id = $createResponse[0]->id;
				}
			}
			catch(Exception $e){

			}
		}
		$customer_id = $This_order->getCustomerId();
    //echo $customer_id; die;
		if($customer_id > 0){
			$data =  Mage::helper('eshopsync')->fetchMappingDetails('eshopsync/customer', $customer_id);
			if($data){
				$account_id = $data['sforce_id'];
			}else{
				$account_id = Mage::getModel("eshopsync/customer")->syncSpecificCustomer($client, $customer_id);
			}
		}
		return $account_id;
	}

}
