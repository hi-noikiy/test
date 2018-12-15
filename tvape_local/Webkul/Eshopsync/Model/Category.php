<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Category extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/category');
    }

    public function categoryMapping($data)
    {
  		if(isset($data['created_by'])){
  			$created_by = $data['created_by'];
  		}else{
  			$helper = Mage::helper('eshopsync/connection');
  			$created_by = $helper::$magento_user;
  		}
      $catData = Mage::getModel('eshopsync/category')
                  ->getCollection()
                  ->addFieldToFilter('magento_id',$data['magento_id']);
      if(count($catData)){
        foreach ($catData as $model) {
          if($model['entity_id']){
            // $model = Mage::getModel('eshopsync/category')->load($model['entity_id']);
            $model->setSforceId($data['sforce_id']);
        		$model->setCreatedBy($created_by);
            $model->setErrorHints("");
            $model->setNeedSync("no");
            $model->save();
          }
        }
      }
      else{
    		$model = Mage::getModel('eshopsync/category');
    		$model->setMagentoId($data['magento_id']);
    		$model->setSforceId($data['sforce_id']);
    		$model->setCreatedBy($created_by);
    		$model->save();
      }


  	}

  public function errorMapping($data)
  {
    $catData = Mage::getModel('eshopsync/category')
                ->getCollection()
                ->addFieldToFilter('magento_id',$data['magento_id']);
    if(count($catData)){
      foreach ($catData as $model) {
        if($model['entity_id']){
          // $model = Mage::getModel('eshopsync/category')->load($model['entity_id']);
          $model->setErrorHints($data['error_hints']);
          $model->save();
        }
      }
    }
    else{
      $model = Mage::getModel('eshopsync/category');
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

    public function getCategoryArray($client, $category_id)
    {
		$category = Mage::getModel('catalog/category')->load($category_id);
		$sObject = new stdclass();
		$name = $category->getName();
		$sObject->Name = $name;
		$sObject->webkul_es_mage__Category_ID__c = $category_id;
		$sObject->webkul_es_mage__Page_Title__c = $category->getMetaTitle();
		$sObject->webkul_es_mage__Meta_Keywords__c = $category->getMetaKeywords();
		$sObject->webkul_es_mage__Meta_Description__c = $category->getMetaDescription();
		$sObject->webkul_es_mage__Category_description__c = strip_tags($category->getDescription());

		/*fetching Category parent id*/
		$parent_id = $category->getParentId();
		if($parent_id){
			$data = Mage::helper('eshopsync')->fetchMappingDetails('eshopsync/category',$parent_id);
			if($data){
				$sObject->webkul_es_mage__Parent_Category__c = $data['sforce_id'];
			}
		}
		/*Checking for Category image sync*/
		$image_url = $category->getImageUrl();
		if($image_url){
			$image_id = Mage::helper('eshopsync')->uploadImageToSalesForce($client, 'category', $category_id, $name, $image_url);
			if($image_id){
				$sObject->webkul_es_mage__Category_Image_ID__c = $image_id;
			}
		}

		return $sObject;
	}

	public function exportSpecificCategory($client, $magento_id)
	{
		$sforce_id = false;
		if($client && $magento_id){
			$sObject = $this->getCategoryArray($client, $magento_id);
			try{
				$createResponse = $client->upsert('webkul_es_mage__Category_ID__c',array($sObject),'webkul_es_mage__categories__c');
        // $createResponse = $client->upsert('webkul_es_mage__Category_ID',array($sObject),'webkul_es_mage__categories__c');
				foreach ($createResponse as $res) {
					if($res->success){
						$sforce_id = $res->id;
						$mapping_data = array(
									'magento_id'=>$magento_id,
									'sforce_id'=>$sforce_id,
								);
						$this->categoryMapping($mapping_data);

						Mage::dispatchEvent('catalog_category_eshopsync_after', array(
			                'magento_id'   => $magento_id,
			                'sforce_id' => $sforce_id,
			            ));
					}elseif(isset($res->errors)){
						$message = Mage::helper('eshopsync')->decodeSalesforceLog($res->errors);
						$error = "Export Error, Category Id ".$magento_id." Reason >>".$message;
						Mage::helper('eshopsync')->eshopsyncLog($error);

            $mapping_data = array(
									'magento_id'=>$magento_id,
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
				$error = "Export Error, Category Id ".$magento_id." >>".$e;
				Mage::log($error, null, 'eshopsync_connector.log');
        $mapping_data = array(
              'magento_id'=>$magento_id,
              'error_hints'=>$e->getMessage(),
            );
        $this->errorMapping($mapping_data);
        $res = array(
          'error'  => 1,
          'val' => $e->getMessage(),
        );
        return $res;
			}
		}
    $res = array(
      'error'  => 0,
      'val' => $sforce_id,
    );
    return $res;
		//return $sforce_id;
	}

	public function updateSpecificCategory($client, $mapping_id){
		$response = false;
		if($client && $mapping_id){

			$mapping_model = Mage::getModel('eshopsync/category');
			$mapping =  $mapping_model->load($mapping_id)->getData();
			$sforce_id = $mapping['sforce_id'];
			$magento_id = $mapping['magento_id'];

			$sObject = $this->getCategoryArray($client, $magento_id);
			try{
				$upsert_response = $client->upsert('webkul_es_mage__Category_ID__c',array($sObject),'webkul_es_mage__categories__c');
				foreach ($upsert_response as $res) {
					if($res->success){
						$this->updateMapping($mapping_id);
						Mage::dispatchEvent('catalog_category_eshopsync_after', array(
			                'sforce_id'   => $sforce_id,
			                'magento_id' => $magento_id,
			            ));
						$response = true;
					}elseif(isset($res->errors)){
						$message = Mage::helper('eshopsync')->decodeSalesforceLog($res->errors);
						$error = "Update Error, Category Id ".$magento_id." Reason >>".$message;
						Mage::helper('eshopsync')->eshopsyncLog($error);
					}
				}
			}catch(Exception $e){
				$error = "Update Error, Category Id ".$magento_id." Reason >>".$e;
				Mage::helper('eshopsync')->eshopsyncLog($error);
			}
		}
		return $response;
	}

	public function getMageCategoryArray()
	{
		$Category = array();
		$category_ids = Mage::getModel('catalog/category')->getCollection()->getAllIds();

		foreach ($category_ids as $category_id){
			$cat = Mage::getModel('catalog/category')->load($category_id);
			if ($cat->getLevel() > 0){
				array_push($Category,
				array(
						'value' => $cat->getId(),
						'label'=>Mage::helper('adminhtml')->__($cat->getName()),
						)
				);
			}
		}
		array_unshift($Category, array('label' => Mage::helper('eshopsync')->__('--Select Magento Category--'), 'value' => ''));
	    return $Category;
	}

	public function getSforceCategoryArray()
	{
		// $client = Mage::helper('eshopsync/connection')->getSforceConnection();
		// $this->update_specific_category($client,7);
		// die;

		$Category = array();
		$client = Mage::helper('eshopsync/connection')->getSforceConnection();
		if ($client > 0){

			array_unshift($Category, array('label' => Mage::helper('eshopsync')->__('--Select Forc category--'), 'value' => ''));
			return $Category;
		}else{
			array_push($Category, array('label' => Mage::helper('eshopsync')->__('Not Available(Connection Error)'), 'value' => ''));
			return $Category;
		}
	}

}
