<?php
class HN_Salesforce_Model_Observer {

	/**#@+
     * Configuration pathes use sync data to Salesforce
     */
	const XML_PATH_SYNC_LEAD = 'salesforce/operation/sync_lead';

	const XML_PATH_SYNC_ACCOUNT = 'salesforce/operation/sync_account';

	const XML_PATH_SYNC_CONTACT = 'salesforce/operation/sync_contact';

	const XML_PATH_SYNC_CAMPAIGN = 'salesforce/operation/sync_campaign';

	const XML_PATH_SYNC_PRODUCT = 'salesforce/operation/sync_product';	

	const XML_PATH_SYNC_ORDER = 'salesforce/operation/sync_lead';

	const XML_PATH_SYNC_SUBSCRIBER = 'salesforce/operation/sync_subscriber';

	const XML_PATH_SYNC_CUSTOM_CUSTOMER = 'salesforce/custom/sync_custom_customer';

	const XML_PATH_SYNC_CUSTOM_PRODUCT = 'salesforce/custom/sync_custom_product';
	
	const XML_PATH_SYNC_CUSTOM_INVOICE = 'salesforce/custom/sync_custom_invoice';

	const XML_PATH_SYNC_CUSTOMIZE_CUSTOMER_GROUP = 'salesforce/customize/customer_group';

	public function __construct()
	{
		$this->_account = Mage::getModel('salesforce/sync_account');
		$this->_lead = Mage::getModel('salesforce/sync_lead');
		$this->_contact = Mage::getModel('salesforce/sync_contact');
		$this->_campaign = Mage::getModel('salesforce/sync_campaign');
		$this->_product = Mage::getModel('salesforce/sync_product');
		$this->_order = Mage::getModel('salesforce/sync_order');
		$this->_customcustomer = Mage::getModel('salesforce/sync_customCustomer');
		$this->_customproduct = Mage::getModel('salesforce/sync_customProduct');
		$this->_custominvoice = Mage::getModel('salesforce/sync_customInvoice');
	}

	public function checkGroup($customer)
	{
		$checkGroup = false;

		$groupId = $customer->getGroupId();

		$customize = explode(',',Mage::getStoreConfig(self::XML_PATH_SYNC_CUSTOMIZE_CUSTOMER_GROUP));

		for($i = 0;$i < count($customize); $i++)
		{
			if ($customize[$i] == $groupId)
			{
				$checkGroup = true;
			}
		}

		return $checkGroup;
	}

	public function syncLead(Varien_Event_Observer $observer) {

		/* @var $customer Mage_Customer_Model_Customer */
		$event = $observer->getCustomer();
		$id = $event->getId();

		$checkGroup = $this->checkGroup($event);

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_LEAD && $checkGroup)){
			$this->_lead->sync($id);
		}		

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CUSTOM_CUSTOMER && $checkGroup)){
			$this->_customcustomer->sync($id, true);
		}
	}
	
	public function syncOrder(Varien_Event_Observer $observer) {
		if(!Mage::getStoreConfigFlag(self::XML_PATH_SYNC_ORDER))
			return;

		$event = $observer->getEvent()->getOrder();
		$id= $event->getId();	
		$this->_order->sync($id);
	}

	public function syncProduct(Varien_Event_Observer $observer) {
		
		/* @var $product Mage_Catalog_Model_Product */
		$product = $observer->getProduct();
		$id = $product->getId();
		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_PRODUCT))
			$this->_product->sync($id, true);
	
		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CUSTOM_PRODUCT))
			$this->_customproduct->sync($id, true);
		
	}

	public function deleteProduct(Varien_Event_Observer $observer) {

		/* @var $product Mage_Catalog_Model_Product */
		$product = $observer->getProduct();
		$sku = $product->getSku();

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_PRODUCT))
			$this->_product->delete($sku);			
		
		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CUSTOM_PRODUCT))
			$this->_customproduct->delete($sku);
	}
	public function updateCustomer(Varien_Event_Observer $observer) {
			
		$customer = $observer->getCustomerAddress();
		$id = $customer->getCustomerId();

		$customerData = Mage::getSingleton('customer/customer')->load($id);

		$checkGroup = $this->checkGroup($customerData);

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_LEAD) && $checkGroup)
			$this->_lead->sync($id, true);

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CONTACT) && $checkGroup)
			$this->_contact->sync($id, true);

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_ACCOUNT) && $checkGroup)
			$this->_account->sync($id, true);

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CUSTOM_CUSTOMER) && $checkGroup)
			$this->_customcustomer->sync($id, true);

	}

	public function deleteCustomer(Varien_Event_Observer $observer) {
			
		$customer = $observer->getCustomer();
		$email = $customer->getEmail();

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_ACCOUNT))
			$this->_account->delete($email);

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_LEAD))
			$this->_lead->delete($email);
		
		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CONTACT))
			$this->_contact->delete($email);

		if(Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CUSTOM_CUSTOMER))
			$this->_customcustomer->delete($email);			
	}
	
	public function syncSubscriber(Varien_Event_Observer $observer){

		if(!Mage::getStoreConfigFlag(self::XML_PATH_SYNC_SUBSCRIBER))
			return;

		$event = $observer->getEvent();
		$subscriber = $event->getSubscriber();
		$email = $subscriber->getEmail();		
		$data = [];

		/* Check login */
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			$last_name= $customerData->getLastname();
			$data['FirstName']= $customerData->getFirstname();
		}
		else{
			$last_name = 'Guest';
		} 
		$data['LastName'] = $last_name;
		$data['Email'] = $email ;
		
		$this->_lead->syncByEmail($data);
	}

	public function syncCampaign(Varien_Event_Observer $observer){

		if(!Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CAMPAIGN))
			return;

		$event = $observer->getEvent()->getRule();
		$id = $event->getId();
		$this->_campaign->sync($id);
	}

	public function syncCustomInvoice(Varien_Event_Observer $observer) {

		if(!Mage::getStoreConfigFlag(self::XML_PATH_SYNC_CUSTOM_INVOICE))
			return;

		$event = $observer->getInvoice();
		$id = $event->getId();
		$this->_custominvoice->sync($id);
	}

}