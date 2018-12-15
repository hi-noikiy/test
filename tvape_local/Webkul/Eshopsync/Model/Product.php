<?php //$start = microtime(true); $time_elapsed_secs = microtime(true) - $start;
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Product extends Mage_Core_Model_Abstract
{
	public static $product_type = array(
			'simple' => 'Simple Product',
			'grouped' => 'Grouped Product',
			'configurable' => 'Configurable Product',
			'virtual' => 'Virtual Product',
			'bundle' => 'Bundle Product',
			'downloadable' => 'Downloadable Product',
		);

    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/product');
    }

    public function productMapping($data)
    {
			if(isset($data['created_by'])){
				$created_by = $data['created_by'];
			}else{
				$helper = Mage::helper('eshopsync/connection');
				$created_by = $helper::$magento_user;
			}

			$proData = Mage::getModel('eshopsync/product')
                  ->getCollection()
                  ->addFieldToFilter('magento_id',$data['magento_id']);
      if(count($proData)){
        foreach ($proData as $model) {
          if($model['entity_id']){
            // $model = Mage::getModel('eshopsync/product')->load($model['entity_id']);
            $model->setSforceId($data['sforce_id']);
        		$model->setCreatedBy($created_by);
            $model->setErrorHints("");
            $model->setNeedSync("no");
            $model->save();
          }
        }
      }
      else{
    		$model = Mage::getModel('eshopsync/product');
    		$model->setMagentoId($data['magento_id']);
    		$model->setSforceId($data['sforce_id']);
    		$model->setCreatedBy($created_by);
    		$model->save();
      }
		}

		public function errorMapping($data)
  {
	    $proData = Mage::getModel('eshopsync/product')
	                ->getCollection()
	                ->addFieldToFilter('magento_id',$data['magento_id']);
	    if(count($proData)){
	      foreach ($proData as $model) {
	        if($model['entity_id']){
	          // $model = Mage::getModel('eshopsync/product')->load($model['entity_id']);
	          $model->setErrorHints($data['error_hints']);
	          $model->save();
	        }
	      }
	    }
	    else{
	      $model = Mage::getModel('eshopsync/product');
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

	public function getProductCategoryArray($categoryIds)
	{
		$categories = array();

		foreach($categoryIds as $cat_id){
			$data =  Mage::helper('eshopsync')->fetchMappingDetails('eshopsync/category', $cat_id);
			if($data){
				$sforce_id = $data['sforce_id'];
				array_push($categories, $sforce_id);
			}
		}
	    return $categories;
	}

	public function deleteProductCategoriesMapping($client, $sforce_id)
	{
		if ($client && $sforce_id) {
			try
			{
				$selectResponse = $client->query("SELECT Id FROM webkul_es_mage__product_categories__c WHERE webkul_es_mage__Product__c = '".(string)$sforce_id."'");
				foreach ($selectResponse->records as $response) {
					$deleteResponse = $client->delete(array($response->Id));
					if (!$deleteResponse[0]->success)
						return false;
				}
				return true;
			}
			catch(Exception $e)
			{}
		}
		return false;
	}

	public function createProductCategoriesMapping($client, $sforce_id, $salesforce_categories_info)
	{
		if ($client && $sforce_id && $salesforce_categories_info) {
			$categories_for_salesforce = array();
			foreach ($salesforce_categories_info as $salesforce_cat_id) {
				$sObject = new stdclass();
				$sObject->webkul_es_mage__Product__c = $sforce_id;
				$sObject->webkul_es_mage__categories__c = $salesforce_cat_id;
				$categories_for_salesforce[] = $sObject;
			}

			if ($categories_for_salesforce) {
				try
				{
					$createResponse = $client->create($categories_for_salesforce, 'webkul_es_mage__product_categories__c');
					foreach ($createResponse as $response) {
						if (!$response->success)
							return false;
					}
					return $createResponse;
				}
				catch(Exception $e)
				{}
			}
		}
		return false;
	}

	public function deleteProductSelectedPriceBookEntry($client, $sforce_id, $selected_pricebook_id)
	{
		if ($client && $sforce_id && $selected_pricebook_id) {
			try
			{
				$response = $client->query("SELECT Id FROM PricebookEntry WHERE Product2Id = '".(string)$sforce_id."' AND Pricebook2Id != '".(string)$selected_pricebook_id."'");
				foreach ($response->records as $queryResult) {
					$deleteResponse = $client->delete(array($queryResult->Id));
					if (!$deleteResponse[0]->success)
						return false;
				}
				return true;
			}
			catch(Exception $e)
			{}
		}
		return false;
	}

	public function deleteProductStandardPriceBookEntry($client, $sforce_id, $standard_pricebook_id)
	{
		if ($client && $sforce_id) {
			try
			{
				$response = $client->query("SELECT Id FROM PricebookEntry WHERE Product2Id = '".(string)$sforce_id."'");
				foreach ($response->records as $queryResult) {
					$deleteResponse = $client->delete(array($queryResult->Id));
					if (!$deleteResponse[0]->success)
						return false;
				}
				return true;
			}
			catch(Exception $e)
			{}
		}
		return false;
	}

	public function createProductPriceBookEntry($client, $pricebook_id, $id_product, $price, $use_standard_price = false)
	{
		if ($pricebook_id && $client && $id_product && $price) {
			$sObject = new stdClass();
			$sObject->IsActive = 1;
			$sObject->Pricebook2Id = $pricebook_id;
			$sObject->Product2Id = $id_product;
			$sObject->UnitPrice = $price;
			if ($use_standard_price)
				$sObject->UseStandardPrice = true;
			try
			{
				$createResponse = $client->create(array($sObject), 'PricebookEntry');

				if ($createResponse[0]->success)
					return $createResponse[0]->id;
				else
					$message = Mage::helper('eshopsync')->decodeSalesforceLog($createResponse[0]->errors);
					$error = "PricebookEntry Error, Product Id ".$id_product." Reason >>".$message;
					Mage::helper('eshopsync')->eshopsyncLog($error);
			}
			catch(Exception $e)
			{}
		}
		return false;
	}

	public function syncSpecificProduct($client, $product_id, $action="Export", $mapping_id = false)
	{
		$status = true;
		$sforce_id = false;
		$product = Mage::getModel('catalog/product')->load($product_id);
		// foreach($product->getOptions() as $option) {
    //         echo 'Option title is: ' . $option->getTitle();
    //         echo '<br>Option type is: ' . $option->getType();
    //         echo '<br>Option values:';
    //         foreach($option->getValues() as $value) {
    //             echo '<br>- ' . $value->getTitle() . ': ' . $value->getPrice();
    //         }
		// 				echo '<br>';
    //     }
		// die;
		$status = $product->getStatus();
		if($status == '2'){
			$status = false;
		}
		$sObject = new stdclass();
		$type = $product->getTypeID();
		if(isset(self::$product_type[$type])){
			$sObject->webkul_es_mage__Magento_Product_Type__c = self::$product_type[$type];
		}
		$name = $product->getName();
		/*salesforce product object creation*/
		$sObject->IsActive = $status;
		$sObject->Name = $name;
		$sObject->Description = $product->getDescription();
		$sObject->webkul_es_mage__Magento_Product_ID__c = $product_id;
		$sObject->webkul_es_mage__Magento_Product_Sku__c = $product->getSku();
		if($product->getWeight())
			$sObject->webkul_es_mage__Magento_Product_Weight__c = $product->getWeight();
		$sObject->webkul_es_mage__Magento_Meta_Titile__c = $product->getMetaTitle();
		$sObject->webkul_es_mage__Magento_Meta_Keywords__c = $product->getMetaKeyword();
		$sObject->webkul_es_mage__Magento_Meta_Descritption__c = $product->getMetaDescription();

		$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

		$sObject->webkul_es_mage__Magento_Stock_Availability__c = $stock->getQty();
		$sObject->webkul_es_mage__Magento_Short_Description__c = $product->getShortDescription();
		try{
			$image_path = Mage::helper('catalog/image')->init($product, 'image');
			if($image_path){
				$image_id = Mage::helper('eshopsync')->uploadImageToSalesForce($client, 'product', $product_id, $name, $image_path);
				if($image_id)
					$sObject->webkul_es_mage__Magento_Image_ID__c = $image_id;
			}
		}
		catch(Exception $e) {}
		try{
			$createResponse = $client->upsert('webkul_es_mage__Magento_Product_ID__c',array($sObject),'Product2');
			// echo '<pre>'; print_r($createResponse); die;
			foreach ($createResponse as $res) {
				if($res->success){
					$sforce_id = $res->id;
					/*fetch all product categories and link to salesforce product*/
					$categories_info = $this->getProductCategoryArray($product->getCategoryIds());
					if($categories_info){
						$is_mapping_deleted = $this->deleteProductCategoriesMapping($client, $sforce_id);
						if($is_mapping_deleted){
							$this->createProductCategoriesMapping($client, $sforce_id, $categories_info);
						}
					}
					/*product price synchronization */
					$price = $product->getPrice();
					$selected_pricebook_id =  Mage::getStoreConfig('eshopsync/default/pricebook');
					$standard_pricebook_id =  Mage::getStoreConfig('eshopsync/default/standard_pricebook');
					$is_selected_price_deleted = $this->deleteProductSelectedPriceBookEntry($client, $sforce_id, $standard_pricebook_id);
					if($is_selected_price_deleted){
						$is_standard_price_deleted = $this->deleteProductStandardPriceBookEntry($client, $sforce_id, $standard_pricebook_id);
						if($is_standard_price_deleted){
							$is_entry_created = $this->createProductPriceBookEntry($client, $standard_pricebook_id, $sforce_id, $price);
							if ($is_entry_created) {
								$this->createProductPriceBookEntry($client, $selected_pricebook_id, $sforce_id, $price);
							}
						}
					}

					/*mapping entry if action is export*/
					if($action == "Export"){
						$mapping_data = array(
								'magento_id'=>$product_id,
								'sforce_id'=>$sforce_id,
							);
						$this->productMapping($mapping_data);
					}
					elseif ($action == "Update") {
						$this->updateMapping($mapping_id);
					}
					/*dispatch event after category synchronization*/
					Mage::dispatchEvent('catalog_product_eshopsync_after', array(
						'magento_id'   => $product_id,
						'sforce_id' => $sforce_id,
					));
				}elseif(isset($res->errors)){
					$message = Mage::helper('eshopsync')->decodeSalesforceLog($res->errors);
					$error = $action." Error, Product Id ".$product_id." Reason >>".$message;
					Mage::helper('eshopsync')->eshopsyncLog($error);

					$mapping_data = array(
								'magento_id'=>$product_id,
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
		}catch(Exception $e){
			$error = $action." Error, Product Id ".$product_id." >>".$e;
			Mage::log($error, null, 'eshopsync_connector.log');

			$mapping_data = array(
						'magento_id'=>$product_id,
						'error_hints'=>$e->getMessage(),
					);
			$this->errorMapping($mapping_data);
			$res = array(
				 'error'  => 1,
				 'val' => $e->getMessage(),
			 );
			return $res;
		}
    //return $sforce_id;
		$res = array(
      'error'  => 0,
      'val' => $sforce_id,
    );
    return $res;
	}

}
