<?php
class EM_Apiios_Model_Observer {
	public function beforeCatalogProductCollectionLoad(Varien_Event_Observer $observer) {
		$request = Mage::app()->getRequest();
		$handle = $request->getRouteName().'_'.$request->getControllerName().'_'.$request->getActionName();
		if(($handle != 'catalog_product_view') && ($exclude = trim(Mage::getStoreConfig('apiios/general/exclude_products_sku'),','))){		
			$observer->getEvent()->getCollection()->addAttributeToFilter(array(
				 array(
					'attribute' => 'sku',
					'null'        => true
				 ),
				 array(
					'attribute' => 'sku',
					'nin'        => explode(',',$exclude)
				 )	
			));
		}
	}
}
?>