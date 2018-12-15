<?php
class HN_Salesforce_Model_Sync_CustomInvoice extends HN_Salesforce_Model_Connector{

	const XML_PATH_SALESFORCE_CUSTOM_INVOICE = 'salesforce/custom/invoice';
	const XML_PATH_SALESFORCE_CUSTOM_INVOICE_ITEM = 'salesforce/custom/invoice_item';

	public function __construct() {
		parent::__construct();
		$this->_type = Mage::getStoreConfig(self::XML_PATH_SALESFORCE_CUSTOM_INVOICE);
		$this->_item = Mage::getStoreConfig(self::XML_PATH_SALESFORCE_CUSTOM_INVOICE_ITEM);
		if(!$this->_type || !$this->_item)
			return;
		$this->_table = 'invoice';			
	}

	/**
	 * Create new a record
	 *
	 * @param int $id
	 * @param boolean $update
	 * @param Mage_Sales_Model_Order_Invoice $model
	 * @param boolean $check
	 * @return string
	 */
	public function sync($id, $update = false, $model=null, $check = true)
	{
		if(!$model)
			$model = Mage::getModel('sales/order_invoice')->load($id);

        $incrementId = $model->getIncrementId();

        $params = $this->_data->getInvoice($model, $this->_type);	
        $params += ['Name' => $incrementId];
        $invoiceId = $this->createRecords($this->_type, $params);

        /*
		 * Add new record to OrderItem need:
		 * 1. productId
		 * 2. pricebookEntryId
		 * */
		foreach ($model->getAllItems() as $item){

			$product_id = $item->getProductId();
			$name = $item->getName();
			$price = $item->getPrice();
			$qty = $item->getQty();

			if($price > 0){
				// 5. Get productId
				$productId = Mage::getModel('salesforce/sync_product')->sync($product_id);
				$output = [
								'Name' => $name,
								'InvoiceId__c' => $invoiceId,
								'Product2Id__c' => $productId,
								'Quantity__c' => $qty,
								'UnitPrice__c' => $price
				];
				// 6. Add Record to InvoiceItem table
				$this->createRecords($this->_item, $output);
			}
		}

		return $invoiceId;
	}
}
