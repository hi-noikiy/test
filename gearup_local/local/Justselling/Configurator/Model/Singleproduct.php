<?php

/**
* justselling Germany Ltd. EULA
* http://www.justselling.de/
* Read the license at http://www.justselling.de/lizenz
*
* Do not edit or add to this file, please refer to http://www.justselling.de for more information.
*
* @category    justselling
* @package     justselling_configurator
* @copyright   Copyright (C) 2013 justselling Germany Ltd. (http://www.justselling.de)
* @license     http://www.justselling.de/lizenz
**/

class Justselling_Configurator_Model_Singleproduct 
extends Mage_Core_Model_Abstract
implements Justselling_Configurator_Model_Jobprocessor_Processor
{	
	/** @var Job Status constants */
	const STATUS_UNPROCESSED     = 0;
	const STATUS_FINISHED        = 1;
	const STATUS_ERROR        	 = 2;	
	
	const JOBS_TO_RUN			 = 10;
	
	const ATTRIBUTE_NOFILTER	 = 0;
	const ATTRIBUTE_FILTER	 	 = 1;
	
	protected function _construct()
	{
		parent::_construct();
		$this->_init('configurator/singleproduct');
	}
	
	public function createAttributeSet($templateId) {
		$name = "configurator_template_".$templateId;
		
		try {
			$set = Mage::getModel('eav/entity_attribute_set')
			->setEntityTypeId(4)
			->setAttributeSetName($name)
			->setSortOrder(0);
			
			$set->validate();
			$set->save();
			
			$set->initFromSkeleton(4)->save();
		} catch (Mage_Core_Exception $e) {
			/* Try to load existing set */
			$sets = Mage::getResourceModel('eav/entity_attribute_set_collection');
			$sets->addFieldToFilter("entity_type_id", 4);
			$sets->addFieldToFilter("attribute_set_name", $name);
			$set = $sets->getFirstItem();
			if ($set->getId()) {
				// Mage::Log("set exist id is ".$set->getId());
				return $set->getId();
			}
			Mage::Log("Problem creatring attribute set: ".$e->getMessage());
			return 0;
		} catch (Exception $e) {
			Mage::Log("General Problem creatring attribute set: ".$e->getMessage());
			return 0;
		}
		//Mage::Log("ok, id is ".$set->getId());
		return $set->getId();
	}

	public function addGroupToAttributeSet($setId)
	{
		Mage::Log("addGroupToAttributeSet ".$setId);
		$groupName = "Product Configurator";
		
		try {
			$group = Mage::getModel('eav/entity_attribute_group')
			->setAttributeSetId($setId)
			->setAttributeGroupName($groupName)
			->setSortOrder(1)
			->setdefaultId(1);
			
			if ($group->itemExists()) {
				// Mage::Log("group already exist");
				$groups = Mage::getResourceModel('eav/entity_attribute_group_collection');
				$groups->addFieldToFilter("attribute_set_id", $setId);
				$groups->addFieldToFilter("attribute_group_name", $groupName);
				$group = $groups->getFirstItem();
				if ($group->getId()) {
					// Mage::Log("group exist id is ".$group->getId());
					return $group->getId();
				}
			} else {
				// Mage::Log("group is new");
				$group->save();
			}
		} catch (Mage_Core_Exception $e) {
			Mage::Log("Problem creatring attribute set: ".$e->getMessage());
			return 0;
		} catch (Exception $e) {
			Mage::Log("General Problem creatring attribute set: ".$e->getMessage());
			return 0;
		}

		return $group->getId();
	}
	
	public function addAttributeToAttributeSet($setId, $groupId, $name, $label, $be_type, $fe_type, $position = 0, $values = array(), $mode = self::ATTRIBUTE_NOFILTER)
	{
		/* Check if attribute already exist */
		$attrib = Mage::getModel('eav/entity_attribute')->loadByCode("4", $name);
		if (!$attrib->getId()) {
			/* new attribute */
			try {
				/* create new attribute */
				Mage::Log("new attribute adding");
				$attrib = Mage::getModel ( 'eav/entity_attribute' )
				->setEntityTypeId ( 4 )
				->setAttributeCode ( $name )
				->setFrontendLabel ( $label )
				->setBackendType ( $be_type )
				->setFrontendInput ( $fe_type )
				->setIsRequired ( '0' )
				->setIsUserDefined ( '1' )
				->setIsUnique ( '0' )
				->setUsedInProductListing ( '1' )
				
				->setPosition($position);
				
				if ($mode == self::ATTRIBUTE_FILTER) {
					$attrib
						->setIsVisibleOnFront ( 1 )
						->setIsFilterable(1) // Filterable with results
						->setIsFilterableInSearch(1);
				}
				
				$attrib->save ();				
				
				/* assign new attribute to set and group */
				$assign = Mage::getModel ( 'eav/entity_setup', 'core_setup' );
				$assign->addAttributeToSet ( 4, $setId, $groupId, $attrib->getId (), 0 );
				
			}	catch ( Mage_Core_Exception $e ) {
				Mage::Log("problem adding attribute: ".$e->getMessage ());
				return false;
			} catch ( Exception $e ) {
				Mage::Log("problem adding attribute: ".$e->getMessage ());
				return false;
			}
		}
		
		if (sizeof ( $values )) {
			try {
				/* add atribute values */
				Mage::Log ( "adding attribute values " . var_export ( $values, true ) );
				if ($values) {
					foreach ( $values as $value ) {
						Mage::Log ( "value " . $value );
						$this->_createOrGetAttributeOption ( $value, $name );
					}
				}
			} catch ( Mage_Core_Exception $e ) {
				Mage::Log ( "problems assigning attribute: " . $e->getMessage () );
				return false;
			} catch ( Exception $e ) {
				Mage::Log ( "problems assigning attribute: " . $e->getMessage () );
				return false;
			}
		}
		
		return $attrib->getId ();
	}
	
	/**
	* Creates the option if it not already exists and returns the option value (not the label)
	* to be used on product creation.
	*/
	
	private function _createOrGetAttributeOption($name, $attributeName) {
		$attribute_model = Mage::getModel('eav/entity_attribute');
		$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
		$attribute_code = $attribute_model->getIdByCode('catalog_product', $attributeName);
		$attribute = $attribute_model->load($attribute_code);
		$attribute_table = $attribute_options_model->setAttribute($attribute);
		$options = $attribute_options_model->getAllOptions(false);
		$optionValue = $this->_getAttributeOptionValueFor($options, $name);
		if (!$optionValue) {
			$value['option'] = array($name,$name);
			$result = array('value' => $value);
			$attribute->setData('option',$result);
			$attribute->save();
			$attribute_options_model = Mage::getModel('eav/entity_attribute_source_table') ;
			$attribute_table = $attribute_options_model->setAttribute($attribute);
			$options= $attribute_options_model->getAllOptions(false);
			$optionValue = $this->_getAttributeOptionValueFor($options, $name);
		}
	
		return $optionValue;
	}
	
	/**
	 *
	 * @param array $options        	
	 * @param unknown_type $label        	
	 * @return unknown
	 */
	private function _getAttributeOptionValueFor(array $options, $value) {
		foreach ( $options as $option ) {
			$label = strtolower ( $option ['label'] );
			if ($label == strtolower ( trim ( $value ) )) {
				return $option ['value'];
			}
		}
		return false;
	}
	
	/* Implement Job Processor Methods */
	/* Check input parameters */
	public function isValid($params) {
		if (!isset($params["job_id"]) ||
			!isset($params["template_id"]) ||
			!isset($params["base_product_id"]) ||
			!isset($params["attribute_set_id"]) ||
			!isset($params["products"]))
				return false;
		
		return true;
	}
	
	/* Generate jobs to process, returns number of jobs to process */
	public function getTotalToProcess($params) {			
		/* Add jobs for each product to generate */
		$totaltoprocess = 0;
		$job_id = $params["job_id"];
		$template_id = $params["template_id"];
		$template = Mage::getModel("configurator/template");
		
		foreach ($params["products"] as $job) {
			
			/* Render the template's options tree, check blacktlist */
			$job = $template->setDefaultValues($template_id, $job);
			$options_before = sizeof($job);
			$tree = $template->getTree($template_id);
			$tree = $template->renderTree($tree, $job);
			$options_after = sizeof($job);
			
			if ($options_before == $options_after) {
				$item_model = Mage::getModel("configurator/singleproduct_job");
				$item_model->setTemplateId($template_id);
				$item_model->setJobId($job_id);
				$item_model->setConfig(serialize($job));
				$item_model->setStatus(self::STATUS_UNPROCESSED);
				$item_model->save();
				$totaltoprocess++;
			}
		}
		
		return $totaltoprocess;
	}
	
	/* read jobs to process and call callback after a set of jobs */
	/* If there are no more jobs to run call finalize_job on callback */
	public function process($params, Justselling_Configurator_Model_Jobprocessor_Callback $callback) {
		$items = Mage::getModel("configurator/singleproduct_job")->getCollection();
		$items->addFieldToFilter("job_id", $params["job_id"]);
		$items->addFieldToFilter("status", self::STATUS_UNPROCESSED);
		$items->getSelect()->limit(self::JOBS_TO_RUN);
		
		/* check if we have more jobs to do */
		$finalize = false;
		if ($items->count() < self::JOBS_TO_RUN) {
			$finalize = true;
		}
		
		/* procces jobs */
		$processed = 0;
		$problems = 0;
		$template = Mage::getModel("configurator/template")->load($params["template_id"]);
		$baseproduct = Mage::getModel("catalog/product")->load($params["base_product_id"]);
		$options = array();
		$values = array();
		foreach($items as $item) {
			try {
				$data = unserialize($item->getConfig());
				ksort($data);
				
				if (!count($options)) {
					foreach ($data as $option_id => $value_id) {
						$option = Mage::getModel("configurator/option")->load($option_id);
						$options[$option_id] = $option;
					}
				}
				
				if ($this->_createProduct($template, $options, $values, $baseproduct, $params["attribute_set_id"], $params["stock_amount"], $data)) {
					$item->setStatus(self::STATUS_FINISHED);
					$item->save();
					$processed++;
				} else {
					$item->setStatus(self::STATUS_ERROR);
					$item->save();
					$problems++;					
				}
			} catch ( Exception $e ) {
				$problems++;
			} 
		}
		
		/* set job processor status */
		$callback->addProcessed($processed);
		if ($problems > 0)
			$callback->addProblems($problems);
		if ($finalize) 
			$callback->finalizeJob();
		
		return true;	
	}
	
	/* If there are any tasks to do after the job was finalaized do it here */
	public function finalize($params) {
		return true;
	}
	
	protected function _createProduct($template, $options, &$values, $baseproduct, $attribute_set_id, $stock_amount, $params) {
		
		$product = new Mage_Catalog_Model_Product();
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
				
		/* SKU */
		$customOptionModel = Mage::getModel('configurator/product_option_type_custom');
		$option_sku = $customOptionModel->getOptionSku($params,"-");
		$sku = $baseproduct->getSku()."-".$option_sku;
		
		/* Check, if product already exist */
		$update = false;
		$productid = Mage::getModel('catalog/product')->getIdBySku($sku);
		if ($productid) {
				$update = true;
				$product->load($productid);
		}
		
		/* Set Products SKU */
		$product->setSku($sku);
		
		/* Product Name */
		$name = $template->getTitle();
		foreach ($params as $option_id => $value_id) {
			if (!isset($values[$value_id])) {
				$value = Mage::getModel("configurator/value")->load($value_id);
				$values[$value_id] = $value; 
			} else { 
				$value = $values[$value_id];
			}
			$name .= ", ".$value->getTitle();
		}
		$product->setName($name);
		
		/* Set product options */
		foreach ($params as $option_id => $value_id) {
			$value_title = $values[$value_id]->getTitle();
			$attr_option_id = NULL;
			$option_code = substr("prodconf_".$template->getId()."_".$options[$option_id]->getAltTitle(), 0, 30);
			$attr_model = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product', $option_code);
			if ($attr_model) {
				$attr_options = $attr_model->getSource()->getAllOptions(false);
				if ($attr_options) {
					foreach ($attr_options as $attr_option) {
						if ($attr_option["label"] == $value_title) {
							$attr_option_id = $attr_option["value"];
							break;
						}
					}
				}
			}
			if ($attr_option_id)
				$product->setData($option_code, $attr_option_id);
		}
		
		/* Price */
		$customOptionModel = Mage::getModel('configurator/product_option_type_custom');
		$price = $customOptionModel->getOptionPrice($params);
		$product->setPrice($baseproduct->getPrice()+ $price);
		
		$categoryIds = $baseproduct->getCategoryIds();
		$product->setCategoryIds($categoryIds); 
		$product->setWebsiteIDs($baseproduct->getWebsiteIDs()); 
		$product->setDescription($baseproduct->getDescription());
		$product->setShortDescription($baseproduct->getShortDescription());
		$delivery_time =  $baseproduct->getDeliveryTime();
		$product->setData("delivery_time", $delivery_time);;
		$product->setWeight($baseproduct->getWeight());
		$product->setTypeId('simple');
		$product->setAttributeSetId($attribute_set_id);
		$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
		$product->setStatus(1);
		$product->setTaxClassId($baseproduct->getTaxClassId()); 
		$product->setGenerateMeta(1);
		$product->setData("generate_meta", "1");

		$linked_prods = $template->getLinkedProducts($template->getId());
		$product_option_id = NULL;
		foreach ($linked_prods as $prod_id => $prod_option_id) {
			if ($prod_id == $baseproduct->getId())
					$product_option_id = $prod_option_id;
		}
		
		if ($product_option_id)
			$product->setData("prodconf_deeplink", $baseproduct->getProductUrl() . "?" . Mage::getModel("configurator/option")->getDeeplink($product_option_id, $params));
		
		if (!$update) {
			$product->setCreatedAt(strtotime('now'));
			$product->setStockData(array(
				'is_in_stock' => 1,
				'qty' => $stock_amount
			));
		} else {
			$product->setUpdatedAt(strtotime('now'));
		}
		
		/*try {
			$product->save();
			$product = Mage::getModel("catalog/product")->load($product->getId());
		}
		catch (Exception $ex) {
			return false;
		}*/
		
		/* Add product image */
		if (!$update) {
			/* Add additional images */
			$images = $baseproduct->getMediaGalleryImages();
			foreach($images as $image) {
				$product->addImageToMediaGallery($image->getPath(),NULL,false,false);
			}
			
			if ($template->getCombinedProductImage()) {
				/* Add combined product image */
				$product->addImageToMediaGallery(
						Mage::helper('configurator/Combinedimage')->getCombinedProductImage($baseproduct, $template, $params, NULL, Justselling_Configurator_Helper_Combinedimage::GET_PATH) ,
						array('thumbnail','small_image','image'),
						false,false
				);
			} else {
				/* Add media images from Base product */
				$images = array();
				if ($baseproduct->getImage())
					$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . "catalog/product" . $baseproduct->getImage(),"image",false,true);
				if ($baseproduct->getSmallImage())
					$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . "catalog/product" . $baseproduct->getSmallImage(),"small_image",false,true);
				if ($baseproduct->getThumbnail())
					$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . "catalog/product" . $baseproduct->getThumbnail(),"thumbnail",false,true);
			}
		}

		/* Save product image gallery */
		try {
			$product->save();
		}
		catch (Exception $e) {
			return false;
		}
		
		$product = Mage::getModel("catalog/product")->load($product->getId());
		return $product->getId();	
	}
}
