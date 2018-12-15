<?php
/**
 * @category   Webkul
 * @package    Magento-Salesforce-Connector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
class Webkul_Eshopsync_Model_Observer extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('eshopsync/observer');
    }

	public function addMassaction($observer)
	{
		$block = $observer->getEvent()->getBlock();
		if(!Mage::getStoreConfig('advanced/modules_disable_output/Webkul_Eshopsync') &&
			Mage::getSingleton('admin/session')->isAllowed('admin/webkul_eshopsync'))
		{
		    if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract){
				if(in_array($block->getRequest()->getControllerName(),array('sales_order','adminhtml_sales_order'))){
					$block->addItem('sync_order', array(
					    'label' => Mage::helper('catalog')->__('Synchronize At Salesforce'),
					    'url' => $block->getUrl('eshopsync/adminhtml_order/syncorder', array('_current'=>true)),
					));
				}
		    }
		}
	}

    public function onContactusPost($observer){
    	$data = $observer->getData();
    	$post = $data['controller_action']->getRequest()->getPost();
    	$auto_sync = Mage::getStoreConfig('eshopsync/auto/contactus');
    	if($post && $auto_sync){
    		$client = Mage::helper('eshopsync/connection')->getSforceConnection();
	        if($client){
	        	$sObject = new stdClass();
	        	$sObject->LastName = $post['name'];
    				$sObject->Company = $post['name'];
    				$sObject->Phone = $post['telephone'];
    				$sObject->LeadSource = 'Web';
    				$sObject->Email = $post['email'];
    				$sObject->Description = $post['comment'];
				try{
					$leadResponse = $client->create(array($sObject), 'Lead');
					foreach ($leadResponse as $record) {
						$lead_id = $record->id;
						if($lead_id){
							$post['sforce_id'] = $lead_id;
							$post['created_by'] = $lead_id;
							Mage::getModel("eshopsync/contactus")->contactusMapping($post);
						}
					}
				}catch(Exception $e){
					return false;
				}
	        }
	        return true;
    	}
    }

    public function onCategorySave($observer)
    {
		$category = $observer->getEvent()->getCategory();
		$controller = Mage::app()->getRequest()->getControllerName();
		if($controller == "catalog_category" && $category){
			$mapping_id = 0;
			$cat_id = $category->getId();
			if($cat_id){
				$model = Mage::getModel('eshopsync/category');
				$auto_sync = Mage::getStoreConfig('eshopsync/auto/category');
				$collection = $model->getCollection()
									->addFieldToFilter('magento_id',array('eq'=>$cat_id));
				if(count($collection)){
					foreach ($collection as $map) {
						$mapping_id = $map->getEntityId();
					}
					if(!$auto_sync && $mapping_id){
						$model->updateMapping($mapping_id, 'yes');
					}
				}
				$client = Mage::helper('eshopsync/connection')->getSforceConnection();
				if ($client && $auto_sync){
					$category_model = Mage::getModel('catalog/category')->load($cat_id);
					$category_model->setName($category->getName());
					$category_model->setMetaTitle($category->getMetaTitle());
					$category_model->setMetaKeywords($category->getMetaKeywords());
					$category_model->setMetaDescription($category->getMetaDescription());
					$category_model->setDescription($category->getDescription());
					$category_model->setParentId($category->getParentId());
					$category_model->save();
					if ($mapping_id){
						$model->updateSpecificCategory($client, $mapping_id);
					}else{
						$response = $model->exportSpecificCategory($client, $cat_id);
					}
				}
			}
		}
		return true;
	}

	public function onProductSave($observer)
	{
		$pro_id = $observer->getEvent()->getProduct()->getId();
		$controller = Mage::app()->getRequest()->getControllerName();
		if($controller == "catalog_product" && $pro_id){
			$mapping_id = 0;
			$client = Mage::helper('eshopsync/connection')->getSforceConnection();

			$auto_sync = Mage::getStoreConfig('eshopsync/auto/product');

			$model = Mage::getModel('eshopsync/product');
			$data =  Mage::helper('eshopsync')->fetchMappingDetails('eshopsync/product', $pro_id);
			if($data){
				$mapping_id = $data['entity_id'];

				if(!$auto_sync && $mapping_id){
					$model->updateMapping($mapping_id, 'yes');
				}
			}
			if ($client && $auto_sync){
				$action = "Export";
				if ($mapping_id){
					$action = 'Update';
				}
				/* sync specific product...*/
				$model->syncSpecificProduct($client, $pro_id, $action);
			}
		}
		return true;
	}

	public function onAddressSave($observer)
	{
		$customer_id = $observer->getCustomer()->getEntityId();
		$controller = Mage::app()->getRequest()->getControllerName();
		if(in_array($controller,array('customer','account')) && $customer_id){
			$mapping_id = 0;
			$auto_sync = Mage::getStoreConfig('eshopsync/auto/customer');
			$data =  Mage::helper('eshopsync')->fetchMappingDetails('eshopsync/customer', $customer_id);
			if($data){
				$mapping_id = $data['entity_id'];
				if(!$auto_sync && $mapping_id){
					Mage::getModel('eshopsync/customer')->updateMapping($mapping_id, 'yes');
				}
			}
			$client = Mage::helper('eshopsync/connection')->getSforceConnection();
			if ($client && $auto_sync){
				if ($mapping_id){
					Mage::getModel("eshopsync/customer")->syncSpecificCustomer($client, $customer_id, 'Update');
				}else{
					Mage::getModel("eshopsync/customer")->syncSpecificCustomer($client, $customer_id);
				}
			}
		}
		return true;
	}

	public function afterPlaceOrder($observer)
	{
		$order_sync = Mage::getStoreConfig('eshopsync/auto/order');
		if($order_sync){
			$OrderIds = $observer->getOrderIds();
			if(!$OrderIds){
				return;
			}
			$client = Mage::helper('eshopsync/connection')->getSforceConnection();
			if($client){
				foreach($OrderIds as $lastOrderId){

					/***************** Order Synchronization  *********************/
					Mage::getModel("eshopsync/order")->exportSpecificOrder($client, $lastOrderId);

				}
			}
		}
	}

	public function afterAdminOrderPlace($observer){
		$order_sync = Mage::getStoreConfig('eshopsync/auto/order');
		if($order_sync){
			$lastOrderId = $observer->getOrder()->getId();
			if (!$lastOrderId)
				return;
			$client = Mage::helper('eshopsync/connection')->getSforceConnection();
			if ($client){

				/***************** Order Synchronization  *********************/
				Mage::getModel("eshopsync/order")->exportSpecificOrder($client, $lastOrderId);

			}
		}
	}


}
