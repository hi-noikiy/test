<?php
/**
 * Magmodules.eu - http://www.magmodules.eu - info@magmodules.eu
 * =============================================================
 * NOTICE OF LICENSE [Single domain license]
 * This source file is subject to the EULA that is
 * available through the world-wide-web at:
 * http://www.magmodules.eu/license-agreement/
 * =============================================================
 * @category    Magmodules
 * @package     Magmodules_Alternatelang
 * @author      Magmodules <info@magmodules.eu>
 * @copyright   Copyright (c) 2015 (http://www.magmodules.eu)
 * @license     http://www.magmodules.eu/license-agreement/  
 * =============================================================
 */
 
class Magmodules_Alternatelang_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getAlternateUrls() {		

		if(Mage::getStoreConfig('alternatelang/general/enabled')) {			

			$alternate_urls = array();
			$router = Mage::app()->getRequest()->getRouteName();				
			$controller = Mage::app()->getRequest()->getControllerName(); 				
			$storeId = Mage::app()->getStore()->getStoreId();
			$storeScope = Mage::getStoreConfig('alternatelang/targeting/language_scope');		
			$stores = Mage::getModel('core/store')->getCollection()->addFieldToFilter('is_active', 1);
			
			if($storeScope == 'store') {
				$stores = $stores->addFieldToFilter('group_id', Mage::app()->getStore()->getGroupId());
			}
			
			if($storeScope == 'website') {
				$stores = $stores->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId());
			}
						
			if(count($stores) > 1) {

				// CMS PAGES
				if(($router == 'cms') && (Mage::getStoreConfig('alternatelang/config/cms')) && ($controller != 'index')) {								
					$cms_identifier = Mage::getBlockSingleton('cms/page')->getPage()->getIdentifier();
					$cms_category = Mage::getBlockSingleton('cms/page')->getPage()->getAlternateCategory();					
					$cms_id = Mage::getBlockSingleton('cms/page')->getPage()->getId();					
					foreach ($stores as $store) {								
						$url = '';						
						if(Mage::getStoreConfig('alternatelang/config/cms', $store->getId())) {							
							$page = Mage::getModel('cms/page')->setStoreId($store->getId())->load($cms_id);
							$cat = Mage::getModel('cms/page')->setStoreId($store->getId())->load($cms_category, 'alternate_category');
				
							if($page->getIdentifier())
								$url = $store->getBaseUrl() . $page->getIdentifier();

							if($cat->getIdentifier())
								$url = $store->getBaseUrl() . $cat->getIdentifier();							
						}														
						if($url) {															
							if($lang_tag = Mage::getStoreConfig('alternatelang/language/hreflang', $store->getId())) {
								$alternate_urls[$lang_tag] = $url;
							} else {
								$store_locale = substr(Mage::getStoreConfig('general/locale/code', $store->getId()),0,2);
								$alternate_urls[$store_locale] = $url;
							}
						}
					}					
				}

				// HOME PAGE
				if(($router == 'cms') && (Mage::getStoreConfig('alternatelang/config/homepage')) && ($controller == 'index')) {									
					foreach ($stores as $store) {								
						if(Mage::getStoreConfig('alternatelang/config/homepage', $store->getId())) {							
							if($lang_tag = Mage::getStoreConfig('alternatelang/language/hreflang', $store->getId())) {
								$alternate_urls[$lang_tag] = $store->getBaseUrl();
							} else {
								$store_locale = substr(Mage::getStoreConfig('general/locale/code', $store->getId()),0,2);
								$alternate_urls[$store_locale] = $store->getBaseUrl();
							}								
						}							
					}
				}				
				
				// PRODUCT PAGE
				if(($product = Mage::registry('current_product')) && (Mage::getStoreConfig('alternatelang/config/product'))) {				
					foreach ($stores as $store) {		
						if(Mage::getStoreConfig('alternatelang/config/product', $store->getId())) {											
							if($url = $this->getCoreProductUrl($product->getId(), $store->getId())) {
								$url = $store->getBaseUrl() . $url;
								if($lang_tag = Mage::getStoreConfig('alternatelang/language/hreflang', $store->getId())) {
									$alternate_urls[$lang_tag] = $url;
								} else {
									$store_locale = substr(Mage::getStoreConfig('general/locale/code', $store->getId()),0,2);
									$alternate_urls[$store_locale] = $url;
								}
							}								
						}
						$current = Mage::helper('alternatelang')->getCoreProductUrl($product->getId(), $storeId);
					}
				}			

				// CATEGORY PAGE
				if(($category = Mage::registry('current_category')) && (Mage::getStoreConfig('alternatelang/config/category')) && (!Mage::registry('current_product'))) {
					foreach ($stores as $store) {		
						if(Mage::getStoreConfig('alternatelang/config/category', $store->getId())) {															
							if($url = Mage::helper('alternatelang')->getCoreCategoryUrl($category->getId(), $store->getId())) {
								$url = $store->getBaseUrl() . $url;
								if($lang_tag = Mage::getStoreConfig('alternatelang/language/hreflang', $store->getId())) {
									$alternate_urls[$lang_tag] = $url;
								} else {
									$store_locale = substr(Mage::getStoreConfig('general/locale/code', $store->getId()),0,2);
									$alternate_urls[$store_locale] = $url;
								}
							}								
						}
						$current = Mage::helper('alternatelang')->getCoreCategoryUrl($category->getId(), $storeId);
					}
				}	
			
				if(is_array($alternate_urls)) {				
					return $alternate_urls;
				} 	

			}	
		} 
		return false;
	}

    public function getCoreProductUrl($product_id, $store_id) {
		
		if($this->checkProductVisibility($product_id, $store_id)) {
			if($category = Mage::registry('current_category')) {
				$category_id = $category->getId();
			} else {
				$category_id = '';						
			}					
			   
			$core_url = Mage::getModel('core/url_rewrite');
			$id_path = sprintf('product/%d', $product_id);

			if(($category_id) && (Mage::getStoreConfig('catalog/seo/product_use_categories', $store_id))) {
				if(!Mage::getStoreConfig('alternatelang/config/canonical', $store_id)) {
					$id_path = sprintf('%s/%d', $id_path, $category_id);
				}    
			}
		
			$core_url->setStoreId($store_id);
			$core_url->loadByIdPath($id_path);
			return $core_url->getRequestPath();
		} else {
			return false;
		}
    }

    public function getCoreCategoryUrl($category_id, $store_id) {
		if($this->checkCategogyVisibility($category_id, $store_id)) {
			$core_url = Mage::getModel('core/url_rewrite');
			$id_path = sprintf('category/%d', $category_id);
			$core_url->setStoreId($store_id);
			$core_url->loadByIdPath($id_path);
			return $core_url->getRequestPath();
		} else {
			return false;
		}	
    }    

    public function checkProductVisibility($product_id, $store_id) {
    	$_productshop = Mage::getModel('catalog/product')->setStoreId($store_id)->load($product_id);  
		if(($_productshop->getStatus() != 1) || ($_productshop->getVisibility() == 1)) {
			return false;
		} else {
			return true;
		}	
    }  
    
    public function checkCategogyVisibility($category_id, $store_id) {
		$_categoryshop = Mage::getModel('catalog/category')->setStoreId($store_id)->load($category_id);
		if(!$_categoryshop->getIsActive()) {
			return false;
		} else {
			return true;
		}	
    }       
     
}