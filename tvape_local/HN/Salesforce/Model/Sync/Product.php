<?php
class HN_Salesforce_Model_Sync_Product extends HN_Salesforce_Model_Connector{
	
	public function __construct() {
		parent::__construct();
		$this->_type = 'Product2';	
		$this->_table = 'product';
	}

	/**
	 * Update or create new a record
	 *
	 * @param int $id
	 * @param boolean $update
	 * @return string
	 */
	public function sync($id, $update = false, $model = null, $check = true) {

		if(!$model)
			$model = Mage::getModel('catalog/product')->load($id);

		$category = Mage::getModel('catalog/category'); 
		$name = $model->getName();
		$code = $model->getSku();
		$price = $model->getPrice();
		$status = $model->getStatus();
		$categoryId = $model->getCategoryIds();
		
		/* 1. Check Product on Product2 table, If not exist then create new */
		if($check)
			$productId = $this->searchRecords($this->_type, 'ProductCode', $code);
		else
			$productId = false;

		if(!$productId || ($update && $productId))
		{
			// 4. Mapping data			
			$params = $this->_data->getProduct($model, $this->_type);

			$params += [
						'Name' => $name,
						'ProductCode' =>  $code,
						'isActive' => $status == 1 ? true : false
					];
	
			if($productId)
				$this->updateRecords($this->_type, $productId, $params);
			else
				$productId = $this->createRecords($this->_type, $params);

			// 5. Add to Pricebook2 table
			$pricebookEntry['Product2Id'] = $productId;
			$pricebookEntry['isActive'] = $params['isActive'];
			$pricebookEntry['Pricebook2Id'] = $this->searchRecords('Pricebook2', 'Name', 'Standard Price Book');
			$pricebookEntry['UnitPrice'] = $price;

			// 6. Add or Update Standard Price
			$pricebookEntryId = $this->searchRecords('PricebookEntry', 'Product2Id', $productId);
			if($update && $pricebookEntryId)
				$this->updateRecords('PricebookEntry', $pricebookEntryId, array('UnitPrice' => $price));
			else
				$this->createRecords('PricebookEntry', $pricebookEntry);
			
			if($categoryId == [] || $update){
				return $productId;
			}else{
				foreach ($categoryId as $key => $value)
				{
					$categoryName = $category->load($value)->getName();
					// 7. Check Category on PriceBook2 table, if not exist then create new
					$categoryId = $this->searchRecords('Pricebook2', 'Name', $categoryName);
					if($categoryId === false){

						$params_category = [
							'Name'=> $categoryName,
							'isActive' => true
						];
						$categoryId = $this->createRecords('Pricebook2', $params_category);
					}
					// 8. Add List Price
					$pricebookEntry['Pricebook2Id'] = $categoryId;
					$this->createRecords('PricebookEntry', $pricebookEntry);
				}
			}
		}	

		return $productId;	
	}

	/**
	 * Delete Record 
	 * @param string $sku
	 */
	public function delete($sku){
		$id = $this->searchRecords($this->_type, 'ProductCode', $sku);
		if($id)
			$this->deleteRecords($this->_type, $id);
	}

}
