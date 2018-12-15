<?php
class HN_Salesforce_Model_Sync_Order extends HN_Salesforce_Model_Connector{

	public function __construct() {
		parent::__construct();
		$this->_type = 'Order';
		$this->_table = 'order';
	}

	/**
	 *  Create new a record
	 *
	 * @param int $id
	 * @param boolean $update
	 * @param Mage_Sales_Model_Order $model
	 * @param boolean $check
	 * @return string
	 */
	public function sync($id, $update = false, $model = null, $check = true) {
		if(!$model)
			$model = Mage::getModel('sales/order')->load($id);

		$customerId = $model->getCustomerId();
		$date = date('Y-m-d', strtotime($model->getCreatedAt()));
		$email = $model->getCustomerEmail();

		$account = Mage::getModel('salesforce/sync_account');
		$contact = Mage::getModel('salesforce/sync_contact');
		
		/* 
		 * 1. Get accountId, create new if not exist
		 * 2. Create new Contacts if not exist
		 */
		if($customerId){
			$accountId = $account->sync($customerId);
			if(Mage::getStoreConfigFlag(HN_Salesforce_Model_Observer::XML_PATH_SYNC_CONTACT))
				$contact->sync($customerId);
		}
		else
		{
			$accountId = $account->syncByEmail($email);
			$data = [
				'Email' => $email,
				'FirstName' => $model->getCustomerFirstname(),
				'LastName' => $model->getCustomerLastname(),
			];		
			if(Mage::getStoreConfigFlag(HN_Salesforce_Model_Observer::XML_PATH_SYNC_CONTACT))				
				$contact->syncByEmail($data);
		}

		$params = $this->_data->getOrder($model, $this->_type);

		/*  Get pricebookId of "Standard Price Book" */
		$pricebookId= $this->searchRecords('Pricebook2', 'Name', 'Standard Price Book');

		/*
		 * Require Field:
		 *
		 * 1. AccountId
		 * 2. EffectiveDate
		 * 3. Status
		 * 4. PriceBook2Id
		 *
		 * */
		$params += [
				'AccountId' => $accountId,
				'EffectiveDate' => $date,
				'Status' => 'Draft',
				'Pricebook2Id'=> $pricebookId,
				];
				
		// 3. Create new Order
		$orderId = $this->createRecords($this->_type, $params);

		/*
		 * Add new record to OrderItem need:
		 * 1. productId
		 * 2. pricebookEntryId
		 * */
		foreach ($model->getAllItems() as $item){

			$product_id = $item->getProductId();
			$product_code = $item->getName();
			$price = $item->getPrice();
			$qty = $item->getQtyOrdered();
			if($price > 0){
				// 5. Get productId
				$productId = Mage::getModel('salesforce/sync_product')->sync($product_id);

				$pricebookEntryId= $this->searchRecords('PricebookEntry', 'Product2Id', $productId);
				$output = [
								'PricebookEntryId' => $pricebookEntryId,
								'OrderId' => $orderId,
								'Quantity' => $qty,
								'UnitPrice' => $price
				];

				// 6. Add Record to OrderItem table
				$this->createRecords('OrderItem', $output);
			}
		}

		return $orderId;
	}
}