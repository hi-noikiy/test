<?php
class HN_Salesforce_Model_Sync_CustomProduct extends HN_Salesforce_Model_Connector{
	
	const XML_PATH_SALESFORCE_CUSTOM_PRODUCT = 'salesforce/custom/product';
	const XML_PATH_SALESFORCE_CUSTOM_PRODUCT_UNIQUE = 'salesforce/custom/unique_product';

	public function __construct() {
		parent::__construct();
		$this->_type = Mage::getStoreConfig(self::XML_PATH_SALESFORCE_CUSTOM_PRODUCT);
		$this->_key = Mage::getStoreConfig(self::XML_PATH_SALESFORCE_CUSTOM_PRODUCT_UNIQUE);
		if(!$this->_type || !$this->_key){
			return;
		}	
		$this->_table = 'product';
	}

	/**
	 * Update or create new a record
	 *
	 * @param int $id
	 * @param boolean $update
	 * @param Mage_Catalog_Model_Product $model
	 * @param boolean $check
	 * @return string
	 */
	public function sync($id, $update = false, $model = null, $check = true)
	{
		if(!$model)
			$model = Mage::getModel('catalog/product')->load($id);
		$sku = $model->getSku();
		
		/* 1. Check Product on Product2 table, If not exist then create new */
		if($check)
			$id = $this->searchRecords($this->_type, $this->_key, $sku);
		else
			$id = false;

		if(!$id || ($update && $id)){

			// 4. Mapping data			
			$params = $this->_data->getProduct($model, $this->_type);
			$params += ['Name' => $model->getName()];

			if($id && $update)
				$this->updateRecords($this->_type, $id, $params);
			else
				$id = $this->createRecords($this->_type, $params);
		}	

		return $id;
	}

	/**
	 * Delete Record
	 *
	 * @param string $sku
	 */
	public function delete($sku){
		$id = $this->searchRecords($this->_type, $this->_key, $sku);
		if($id)
			$this->deleteRecords($this->_type, $id);
	}

}
