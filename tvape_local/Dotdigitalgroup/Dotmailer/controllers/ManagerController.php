<?php 
class Dotdigitalgroup_Dotmailer_ManagerController extends Mage_Adminhtml_Controller_Action
{        
	public $config;
	public $SoapClient;
	public function _construct(){
		$this->SoapClient = new Zend_Soap_Client("http://apiconnector.com/API.asmx?WSDL");
		$this->config = Mage::getStoreConfig('dotmailer');
	}

    public function indexAction(){
		
        $this->loadLayout();
		$this->_setActiveMenu('Dotdigitalgroup_Dotmailer_menu/Dotdigitalgroup_Dotmailer_menu_manager');
		$this->renderLayout();
    }

    public function checkcredentialAction(){
		$username = $this->getRequest()->getParam('username');
		$password = $this->getRequest()->getParam('password');
		$result = true;
		$error = NULL;
		$listAddressBooks = NULL;
		$listCampaigns = NULL;
		
		if($username && $password)
		{
			try {
				$dotm_fields = $this->SoapClient->ListContactDataLabels(array('username' => $username,'password' => $password))->ListContactDataLabelsResult->ContactDataLabel;
				$fields = array(
							'date_stamp'=>'date_stamp',
							'created_at'=>'created_at',
							'customer_id'=>'customer_id',
							'firstname'=>'firstname',
							'lastname'=>'lastname',
							'country_id'=>'country_id',
							'region_id'=>'region_id',
							'region'=>'region',
							'city'=>'city',
							'street'=>'street',
							'telephone'=>'telephone',
							'postcode'=>'postcode',
							'store_id'=>'store_id',
							'website_id'=>'website_id'
						);
				
				foreach($dotm_fields as $field)
					if(in_array(strtolower($field->Name),$fields))
						unset($fields[strtolower($field->Name)]);
				
				foreach($fields as $fieldname)
					try {
						$this->SoapClient->CreateDataField( array("username" => $username, "password" => $password, "fieldname" => $fieldname, "datatype" => "String") );
					} catch (SoapFault $fault) {
					}
				$listAddressBooks = array();
				try {
					$books =  $this->SoapClient->ListAddressBooks(array('username' => $username,'password' => $password))->ListAddressBooksResult->APIAddressBook;
					
					if(is_array($books))
						foreach($books as $book)
							$listAddressBooks[] = array('value' => $book->ID, 'label'=> $book->Name);
					else
						$listAddressBooks[] = array('value' => $books->ID, 'label'=> $books->Name);
				} catch (SoapFault $fault) {
				}
				$listCampaigns = array();
				try {
					$campaigns =  $this->SoapClient->ListCampaigns(array('username' => $username,'password' => $password))->ListCampaignsResult->APICampaign;
					
					if(is_array($campaigns))
						foreach($campaigns as $campaign)
							$listCampaigns[] = array('value' => $campaign->Id, 'label'=> $campaign->Name);
					else
						$listCampaigns[] = array('value' => $campaigns->Id, 'label'=> $campaigns->Name);
				} catch (SoapFault $fault) {
				}

				
			} catch (SoapFault $fault) {
				$result = false;
				$error = $fault->getMessage();//"Wrong API Credentials";
			}
		}
		else
		{
			$result = false;
			$error = "Error settings";
		}

		echo Zend_Json::encode(array('success' => $result, 'error' => $error, 'addressbooks' => $listAddressBooks, 'campaigns' => $listCampaigns));
		
	}
    public function getCustomerData($customer_id)
    {
		
		$data = array();
		$customer = Mage::getSingleton('customer/customer')->load($customer_id);
		$date = new Zend_Date();
		$data['date_stamp'] = $date->toString('YYYY-MM-dd HH:mm:ss');
		$data['created_at'] = $customer->created_at;
		$data['customer_id'] = $customer->getId(); // get custommer id
		$data['firstname'] = $customer->firstname;
		$data['lastname'] = $customer->lastname;
		$data['store_id'] = $customer->store_id;
		$data['website_id'] = $customer->website_id;
		
		
		$customerAddressId = $customer->getDefaultBilling();
		if ($customerAddressId){
			$address = Mage::getModel('customer/address')->load($customerAddressId);
			if($country_id = $address->getData('country_id')) $data['country_id'] = $country_id;
			if($region_id = $address->getData('region_id')) $data['region_id'] = $region_id;
			if($region = $address->getData('region')) $data['region'] = $region;
			if($city = $address->getData('city')) $data['city'] = $city;
			if($street = $address->getData('street')) $data['street'] = $street;
			if($telephone = $address->getData('telephone')) $data['telephone'] = $telephone;
			if($postcode = $address->getData('postcode')) $data['postcode'] = $postcode;
		}
		
		return $data;
    }
	
	public function addToAddressBook($addressbookid, $email, $customerid = NULL)
	{
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password =  $this->config['dotMailer_group']['dotMailer_api_password'];
		$AudienceType = "Unknown";
		$OptInType = "Unknown";
		$EmailType = "Html";
		try {
			$dotmaileraccount = $this->SoapClient->GetContactByEmail(array('username' => $username,'password' => $password, 'email' => $email ))->GetContactByEmailResult;
			if($dotmaileraccount)
			{
				$AudienceType = $dotmaileraccount->AudienceType;
				$OptInType = $dotmaileraccount->OptInType;
				$EmailType = $dotmaileraccount->EmailType;
			}
		} catch (SoapFault $fault) {
		}
		if($customerid)
		{
			$customer_data = $this->getCustomerData($customerid);
			$keys = array();
			$values = array();
			foreach($customer_data as $key => $value)
			{
				$keys[] = $key;
				$values[] = new SoapVar($value, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema");
			}
			$DataFields = array("Keys" => $keys, "Values" => $values);
			$contact = array("ID" => "-1", "Email" => $email, "AudienceType" => $AudienceType, "DataFields" => $DataFields,"OptInType" => $OptInType, "EmailType" => $EmailType);
		}
		else
			$contact = array("ID" => "-1", "Email" => $email, "AudienceType" => $AudienceType, "OptInType" => $OptInType, "EmailType" => $EmailType);

		$params = array("username" => $username, "password" => $password, "contact" => $contact, "addressbookId" => $addressbookid);
		try {
			$this->SoapClient->AddContactToAddressBook($params);
		} catch (SoapFault $fault) {
		}			
		
	}
	
	public function removeFromAddressBook($addressbookid,$email)
	{
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password =  $this->config['dotMailer_group']['dotMailer_api_password'];
		try {
			$contact = array("ID" => "-1", "Email" => $email, "AudienceType" => "Unknown", "OptInType" => "Unknown", "EmailType" => "Html");
			$params = array("username" => $username, "password" => $password, "contact" => $contact, "addressBookId" => $addressbookid, "preventAddressbookResubscribe" => false, "totalUnsubscribe" => false);
			$this->SoapClient->RemoveContactFromAddressBook($params);
		} catch (SoapFault $fault) {
		}			

	}
	
	public function synchronizationAction()
	{
		$this->generaladdressbookid = $this->config['dotMailer_group']['dotMailer_book_general_subscribers'];
		$subscribers = Mage::getModel('newsletter/subscriber')->getCollection()->addFieldToFilter('subscriber_status',1);
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password =  $this->config['dotMailer_group']['dotMailer_api_password'];
		if($subscribers->count() > 0)
		{
			$subscribers_emails = array();
			foreach($subscribers as $subscriber)
				$subscribers_emails[] = $subscriber->getEmail();			
			try {
				$select = 1000;
				$skip = 0;
				$contacts_emails = array();
				while($contacts = $this->SoapClient->ListContactsInAddressBook(array('username' => $username, 'password' => $password, 'addressBookId' => $this->generaladdressbookid, 'select' => $select, 'skip' => $skip))->ListContactsInAddressBookResult->APIContact )
				{
					if(is_array($contacts))
						foreach($contacts as $contact)
							$contacts_emails[] = $contact->Email;
					else
						$contacts_emails[] = $contacts->Email;
					$skip += 1000;
				}
				foreach($subscribers as $subscriber)
					if(!in_array($subscriber->getEmail(),$contacts_emails))
						$this->addToAddressBook($this->generaladdressbookid, $subscriber->getEmail(), $subscriber->getData('customer_id'));
				foreach($contacts_emails as $contacts_email)
					if(!in_array($contacts_email,$subscribers_emails))
						$this->removeFromAddressBook($this->generaladdressbookid,$contacts_email);

			} catch (SoapFault $fault) {
			}
		}
		else
			try {
				$params = array("username" => $username, "password" => $password, "addressBookId" => $this->generaladdressbookid, "preventAddressbookResubscribe" => false, "totalUnsubscribe" => false);
				$this->SoapClient->RemoveAllContactsFromAddressBook($params);
			} catch (SoapFault $fault) {
			}
		echo Zend_Json::encode(array('success'=>true));
	}


	public function getListCampaignActivities($campaignId,$date)
	{
		$campaign_users_emails = array();
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password = $this->config['dotMailer_group']['dotMailer_api_password'];
		$select = 1000;
		$skip = 0;
		try {
			while($campaign_users = $this->SoapClient->ListCampaignActivitiesSinceDate(array('username'=>$username,'password'=>$password, 'campaignId'=>$campaignId, 'startDate' => $date, 'select'=> $select,'skip'=> $skip))->ListCampaignActivitiesSinceDateResult->APICampaignContactSummary)
			{
				if($campaign_users)
					if(is_array($campaign_users))
						foreach($campaign_users as $user)
							$campaign_users_emails[] = $user->Email;
					else
						$campaign_users_emails[] = $campaign_users->Email;
				$skip += 1000;
			}
			
		} catch (SoapFault $fault){
		}
		
		return $campaign_users_emails;
	}
    public function reportAction(){
		
		if($range = $this->getRequest()->getParam('range'))
		{
			$code = Mage::app()->getStore()->getBaseCurrencyCode();
	        $currency = Mage::app()->getLocale()->currency($code);
    	    $currencySymbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();

			$date = new Zend_Date();
			switch($range)
			{
				case '24hours':
					$date = $date->sub('24', Zend_Date::HOUR);
				break;
				case '7days':
					$date = $date->sub('7', Zend_Date::DAY);
				break;
				case 'month':
					$month = $date->toValue(Zend_Date::MONTH);
					$year = $date->toValue(Zend_Date::YEAR);
					$datearray = array('year' => $year, 'month' => $month, 'day' => 1);
					$date = new Zend_Date($datearray);
				break;
				case 'ytd':
					$year = $date->toValue(Zend_Date::YEAR) + 1;
					$datearray = array('year' => $year, 'month' => 1, 'day' => 1);
					$date = new Zend_Date($datearray);
				break;
				case '2ytd':
					$year = $date->toValue(Zend_Date::YEAR);
					$datearray = array('year' => $year, 'month' => 1, 'day' => 1);
					$date = new Zend_Date($datearray);
				break;
			}
			$from_date = $date->toString('YYYY-MM-dd HH:mm:ss');
			$dotm_from_date = $date->toString('YYYY-MM-dd');


			$this->SoapClient = new Zend_Soap_Client("http://apiconnector.com/API.asmx?WSDL");
			$this->config = Mage::getStoreConfig('dotmailer');
	
			$abandoned_carts = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('is_active',1)->addFieldToFilter('items_count', array('gt' => 0))->addFieldToFilter('customer_email', array('neq' => ''))->addFieldToFilter('updated_at', array('from' => $from_date));
			$no_completed_orders =  Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', array('nin' => array('complete','canceled','closed','holded')))->addAttributeToFilter('updated_at', array('from' => $from_date));
			$completed_orders =  Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', 'complete')->addAttributeToFilter('updated_at', array('from' => $from_date));
			
			$abandoned_carts_count = $abandoned_carts->count()+$no_completed_orders->count();
			$completed_orders_count = $completed_orders->count();
			if($completed_orders_count + $abandoned_carts_count != 0)
				$basket_dropout_rate = floor($abandoned_carts_count / ($completed_orders_count + $abandoned_carts_count) *100);
			else
				$basket_dropout_rate = 0;
	
			$lost_revenue = 0;
	
			foreach($abandoned_carts as $cart)
			{
				$items = $cart->getAllItems();
				foreach ($items as $item)
					$lost_revenue += $item->getPrice() * $item->getQty();
			}
			foreach($no_completed_orders as $order)
			{
				$items = $order->getAllItems();
				foreach ($items as $item)
					$lost_revenue += $item->getPrice() * $item->getData('qty_ordered');
			}
	
			$campaign_users_emails = array();
			$lostCartsCampaignId = $this->config['dotMailer_group']['dotMailer_campaign_cart_abandoned'];
			$incompleteOrdersCampaignId = $this->config['dotMailer_group']['dotMailer_campaign_incomplete_order'];
			$campaign_users_lost_carts = $this->getListCampaignActivities($lostCartsCampaignId,$dotm_from_date);
			$campaign_users_incomplete_orders = $this->getListCampaignActivities($incompleteOrdersCampaignId,$dotm_from_date);
			$campaign_users_emails = array_merge($campaign_users_lost_carts,$campaign_users_incomplete_orders);
			if($campaign_users_emails)
			{
				$followed_orders_count = 0;
				$recovered_revenue = 0;
				foreach($completed_orders as $order)
				{
					if(in_array($order->getCustomerEmail(),$campaign_users_emails))
					{
						$followed_orders_count += 1;
						$items = $order->getAllItems();
						foreach ($items as $item)
							$recovered_revenue += $item->getPrice() * $item->getData('qty_ordered');
					}
				}
				
				$recovered_carts_count = $followed_orders_count;
			}
			else
			{
				$recovered_carts_count = 0;
				$recovered_revenue = 0;
			}
	
			if($recovered_carts_count + $abandoned_carts_count != 0)
				$basket_recovery_rate = floor($recovered_carts_count / ($recovered_carts_count + $abandoned_carts_count) *100);
			else
				$basket_recovery_rate = 0;

			echo Zend_Json::encode(array('abandoned_carts' => $abandoned_carts_count,'basket_dropout_rate' => $basket_dropout_rate."%",'lost_revenue' => $currencySymbol.$lost_revenue,
										 'recovered_carts' => $recovered_carts_count,'basket_recovery_rate' => $basket_recovery_rate."%",'recovered_revenue' => $currencySymbol.$recovered_revenue ));
		}
    }


} 