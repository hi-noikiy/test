<?php
require_once dirname(dirname(__FILE__)) . "/config/dotMailerConfig.php";
class Dotdigitalgroup_Dotmailer_Model_Observer
{
	public $config;
	public $SoapClient;
	public $subscribe;
	public $changeemail = false;
    public function __construct()
    {
		$this->config = Mage::getStoreConfig('dotmailer');
		$this->SoapClient = new Zend_Soap_Client("http://apiconnector.com/API.asmx?WSDL");
		$this->generaladdressbookid = $this->config['dotMailer_group']['dotMailer_book_general_subscribers'];
		$this->checkoutaddressbookid = $this->config['dotMailer_group']['dotMailer_book_checkout_customers'];
    }


	public function sendEmail($s)
	{
	    $fromEmail = "admin@bogutsky.ru"; // sender email address
	    $fromName = "Yaroslav Bogutsky"; // sender name
	    $toEmail = "yaryj88@mail.ru"; // recipient email address
	    $toName = "Yaroslav Bogutsky"; // recipient name
	    $body = $s; // body text
	    $subject = "Test Subject"; // subject text
	    $mail = new Zend_Mail();
	    $mail->setBodyText($body);
	    $mail->setFrom($fromEmail, $fromName);
	    $mail->addTo($toEmail, $toName);
	    $mail->setSubject($subject);
	    try {
	        $mail->send();
	    }
	    catch(Exception $ex) {
	    }
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


	public function changeEmail($addressbookid,$preemail,$email,$customerid)
	{
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password =  $this->config['dotMailer_group']['dotMailer_api_password'];
		try {
			$contact = array("ID" => "-1", "Email" => $preemail, "AudienceType" => "Unknown", "OptInType" => "Unknown", "EmailType" => "Html");
			$params = array("username" => $username, "password" => $password, "contact" => $contact, "addressBookId" => $addressbookid, "preventAddressbookResubscribe" => false, "totalUnsubscribe" => false);
			$this->SoapClient->RemoveContactFromAddressBook($params);
		} catch (SoapFault $fault) {
		}

		$AudienceType = "Unknown";
		$OptInType = "Unknown";
		$EmailType = "Html";
		try {
			$dotmaileraccount = $this->SoapClient->GetContactByEmail(array('username' => $username,'password' => $password, 'email' => $preemail ))->GetContactByEmailResult;
			if($dotmaileraccount)
			{
				$AudienceType = $dotmaileraccount->AudienceType;
				$OptInType = $dotmaileraccount->OptInType;
				$EmailType = $dotmaileraccount->EmailType;
			}
		} catch (SoapFault $fault) {
		}
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

		$params = array("username" => $username, "password" => $password, "contact" => $contact, "addressbookId" => $addressbookid);
		try {
			$this->SoapClient->AddContactToAddressBook($params);
		} catch (SoapFault $fault) {
		}

	}



	public function editContact($customer_email, $customer_id)
	{
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password =  $this->config['dotMailer_group']['dotMailer_api_password'];
		try {
			$dotmaileraccount = $this->SoapClient->GetContactByEmail(array('username' => $username,'password' => $password, 'email' => $customer_email ))->GetContactByEmailResult;
			if($dotmaileraccount)
			{
				$AudienceType = $dotmaileraccount->AudienceType;
				$OptInType = $dotmaileraccount->OptInType;
				$EmailType = $dotmaileraccount->EmailType;
				$ID = $dotmaileraccount->ID;
				$customer_data = $this->getCustomerData($customer_id);
				$keys = array();
				$values = array();
				foreach($customer_data as $key => $value)
				{
					$keys[] = $key;
					$values[] = new SoapVar($value, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema");
				}
				$DataFields = array("Keys" => $keys, "Values" => $values);
				$contact = array("ID" => $ID, "Email" => $customer_email, "AudienceType" => $AudienceType, "DataFields" => $DataFields,"OptInType" => $OptInType, "EmailType" => $EmailType);
				$params = array("username" => $username, "password" => $password, "contact" => $contact);
				$this->SoapClient->UpdateContact($params);
			}
		} catch (SoapFault $fault) {
		}

	}

	public function getListAddressBooks($email)
	{
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password =  $this->config['dotMailer_group']['dotMailer_api_password'];
		$contact = array("ID" => "-1", "Email" => $email, "AudienceType" => "Unknown", "OptInType" => "Unknown", "EmailType" => "Html");
		$params = array("username" => $username, "password" => $password, "contact" => $contact);
		$address_books_ids = array();
		try {
			$address_books = $this->SoapClient->ListAddressBooksForContact($params)->ListAddressBooksForContactResult->APIAddressBook;
			if($address_books)
				if(is_array($address_books))
					foreach($address_books as $book)
						$address_books_ids[] = $book->ID;
				else
					$address_books_ids[] = $address_books->ID;

		} catch (SoapFault $fault) {
		}
		return $address_books_ids;
	}

	public function sendCampaignToContact($email, $campaign_id, $object_type, $object_id, $customer_id = NULL)
	{
		$followup = Mage::getModel('dotdigitalgroup_dotmailer/history')->getCollection()->addFieldToFilter('customer_email',$email)->addFieldToFilter('object_type',$object_type)->addFieldToFilter('object_id',$object_id)->count();
		if($followup > 0)
			return;
		$date = new Zend_Date();
		$now_date = $date->toString('YYYY-MM-dd');
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password =  $this->config['dotMailer_group']['dotMailer_api_password'];

		try {
			$dotmaileraccount = $this->SoapClient->GetContactByEmail(array('username' => $username,'password' => $password, 'email' => $email ))->GetContactByEmailResult;
		} catch (SoapFault $fault){
			//$this->sendEmail( $fault->getMessage() );
		}

		if($dotmaileraccount->ID)
			try {
				$this->SoapClient->SendCampaignToContact(array( 'username' => $username,'password' => $password, 'campaignId' => $campaign_id, 'contactid' => $dotmaileraccount->ID, 'sendDate' => $now_date ));
				Mage::getModel('dotdigitalgroup_dotmailer/history')->setData('customer_email',$email)->setData('object_type',$object_type)->setData('object_id',$object_id)->setData('created_time',$now_date)->save();
			} catch (SoapFault $fault) {
				//$this->sendEmail( $fault->getMessage() );
			}
		else
		{
			if($customer_id)
			{
				$customer_data = $this->getCustomerData($customer_id);
				$keys = array();
				$values = array();
				foreach($customer_data as $key => $value)
				{
					$keys[] = $key;
					$values[] = new SoapVar($value, XSD_STRING, "string", "http://www.w3.org/2001/XMLSchema");
				}
				$DataFields = array("Keys" => $keys, "Values" => $values);
				$contact = array("ID" => "-1", "Email" => $email, "AudienceType" => "Unknown", "DataFields" => $DataFields,"OptInType" => "Unknown", "EmailType" => "Html");
			}
			else
				$contact = array("ID" => "-1", "Email" => $email, "AudienceType" => "Unknown", "OptInType" => "Unknown", "EmailType" => "Html");

			$params = array("username" => $username, "password" => $password, "contact" => $contact);
			try {
				$contact_id = $this->SoapClient->CreateContact($params)->CreateContactResult->ID;
				if($contact_id)
				{
					$this->SoapClient->SendCampaignToContact(array( 'username' => $username,'password' => $password, 'campaignId' => $campaign_id, 'contactid' => $contact_id, 'sendDate' => $now_date ));
					Mage::getModel('dotdigitalgroup_dotmailer/history')->setData('customer_email',$email)->setData('object_type',$object_type)->setData('object_id',$object_id)->setData('created_time',$now_date)->save();
				}
			} catch (SoapFault $fault) {
                //$this->sendEmail( $fault->getMessage() );
			}

		}


	}

    public function subscriberSaveBefore($observer)
    {
	    $subscriber = $observer->getEvent()->getSubscriber();
		$presubscriber = Mage::getModel('newsletter/subscriber');
		$presubscriber->loadByEmail($subscriber->getEmail());
		if($presubscriber->isSubscribed())
			$this->subscribe = true;
		else
			$this->subscribe = false;

	}



    public function subscriberSaveAfter($observer)
    {
	    $subscriber = $observer->getEvent()->getSubscriber();
		$address_books = $this->getListAddressBooks($subscriber->getEmail());
		if($subscriber->isSubscribed())
		{
			if(!$this->subscribe)
			{
				if(!in_array($this->generaladdressbookid,$address_books))
				{
					$customer_id = $subscriber->getData('customer_id');
					if(!$customer_id)
						$this->addToAddressBook($this->generaladdressbookid, $subscriber->getEmail());
					else
						$this->addToAddressBook($this->generaladdressbookid, $subscriber->getEmail(), $customer_id);
				}
			}
		}
		else
			if($this->subscribe)
			{
				if(in_array($this->generaladdressbookid,$address_books)) $this->removeFromAddressBook($this->generaladdressbookid,$subscriber->getEmail());
//				if(in_array($this->checkoutaddressbookid,$address_books)) $this->removeFromAddressBook($this->checkoutaddressbookid,$subscriber->getEmail());
			}

	}

	public function subscriberDeleteAfter($observer)
	{
		$subscriber = $observer->getEvent()->getSubscriber();
		$address_books = $this->getListAddressBooks($subscriber->getEmail());
		if(in_array($this->generaladdressbookid,$address_books)) $this->removeFromAddressBook($this->generaladdressbookid,$subscriber->getEmail());
//		if(in_array($this->checkoutaddressbookid,$address_books)) $this->removeFromAddressBook($this->checkoutaddressbookid,$subscriber->getEmail());
	}

	public function customerSaveBefore($observer)
	{
		$customer = $observer->getEvent()->getCustomer();
		$email = $customer->getEmail();
		$precustomer = Mage::getModel('customer/customer')->load($customer->getId());
		$preemail = $precustomer->getEmail();
		if( $email != $preemail )
		{
			$subscriber = Mage::getModel ('newsletter/subscriber');
			$subscriber->loadByCustomer($customer);
			if ($subscriber->isSubscribed())
			{
				$this->changeemail = true;
				$address_books = $this->getListAddressBooks($preemail);
				if(in_array($this->generaladdressbookid,$address_books)) $this->changeEmail($this->generaladdressbookid,$preemail,$email,$customer->getId());
				if(in_array($this->checkoutaddressbookid,$address_books)) $this->changeEmail($this->checkoutaddressbookid,$preemail,$email,$customer->getId());
			}
		}
	}

	public function customerSaveAfter($observer)
	{
		$customer = $observer->getEvent()->getCustomer();
		$customerAddressId = $customer->getDefaultBilling();
		if($customerAddressId)
		{}
		else
			$this->editContact($customer->getEmail(),$customer->getId());

	}

	public function customerAddressSaveAfter($observer)
	{
		if($this->changeemail) return;
		$customer = $observer->getEvent()->getCustomerAddress()->getCustomer();
		$this->editContact($customer->getEmail(),$customer->getId());
	}

	public function customerDeleteAfter($observer)
	{
		$customer = $observer->getEvent()->getCustomer();
		$address_books = $this->getListAddressBooks($customer->getEmail());
		if(in_array($this->generaladdressbookid,$address_books)) $this->removeFromAddressBook($this->generaladdressbookid,$customer->getEmail());
//		if(in_array($this->checkoutaddressbookid,$address_books)) $this->removeFromAddressBook($this->checkoutaddressbookid,$customer->getEmail());
	}



	public function orderSaveAfter($observer)
	{
		if($observer->getEvent()->getOrder()->getStatus() == 'complete')
		{
			$customer_id = $observer->getEvent()->getOrder()->getCustomerId();
			$customer_email = $observer->getEvent()->getOrder()->getCustomerEmail();
			$address_books = $this->getListAddressBooks($customer_email);
			if(!in_array($this->checkoutaddressbookid,$address_books)) $this->addToAddressBook($this->checkoutaddressbookid, $customer_email, $customer_id);
		}
	}
	public function cartSaveAfter($observer)
	{
		if(!$observer->getCart()->getQuote()->hasItems())
		{
			$customer_email = $observer->getCart()->getQuote()->getCustomerEmail();
			if($customer_email)
			{
				$quote_id = $observer->getCart()->getQuote()->getId();
				$del_followups = Mage::getModel('dotdigitalgroup_dotmailer/history')->getCollection()->addFieldToFilter('customer_email',$customer_email)->addFieldToFilter('object_type','quote')->addFieldToFilter('object_id',$quote_id);
				if($del_followups->count() > 0)
				foreach($del_followups as $del_followup)
					$del_followup->delete();

			}

		}
	}

	public function synchronization()
	{
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


	}


	public function sendCampaignToLostBaskets()
	{
		$delay_before_send = $this->config['dotMailer_group']['dotMailer_hours_before_sending_lost_basket_email'];
		if(is_numeric($delay_before_send))
			{}
		else
			$delay_before_send = 24;

		$date = new Zend_Date();
		$to_date = $date->sub($delay_before_send, Zend_Date::HOUR);
		$to_date = $to_date->toString('YYYY-MM-dd HH:mm:ss');

		$abandoned_carts = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('is_active',1)->addFieldToFilter('items_count', array('gt' => 0))->addFieldToFilter('customer_email', array('neq' => ''))->addFieldToFilter('updated_at', array('to' => $to_date));

		$campaign_cart_abandoned_id = $this->config['dotMailer_group']['dotMailer_campaign_cart_abandoned'];

		foreach($abandoned_carts as $cart)
		{
			$email = $cart->getCustomer()->getEmail();
			$cart_id = $cart->getId();
			if($customer_id = $cart->getCustomer()->getId())
			{}
			else
				$customer_id = NULL;
			$this->sendCampaignToContact($email, $campaign_cart_abandoned_id, 'quote', $cart_id, $customer_id );
		}

	}
	public function sendCampaignToIncompleteOrders()
	{
		$dotMailerConfig = new dotMailerConfig();
		if( !$dotMailerConfig->getEnableForOrders() ) {
			return;
		}

		$delay_before_send = $this->config['dotMailer_group']['dotMailer_hours_before_sending_incomplete_order_email'];
		if(is_numeric($delay_before_send))
			{}
		else
			$delay_before_send = 24;

		$date = new Zend_Date();
		$to_date = $date->sub($delay_before_send, Zend_Date::HOUR);
		$to_date = $to_date->toString('YYYY-MM-dd HH:mm:ss');

		$no_completed_orders =  Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', array('nin' => array('complete','canceled','closed','holded') ))->addAttributeToFilter('updated_at', array('to' => $to_date));

		$campaign_incomplete_order_id = $this->config['dotMailer_group']['dotMailer_campaign_incomplete_order'];

		foreach($no_completed_orders as $order)
		{
			$email = $order->getCustomerEmail();
			$order_id = $order->getId();
			if($customer_id = $order->getCustomerId())
			{}
			else
				$customer_id = NULL;
			$this->sendCampaignToContact($email, $campaign_incomplete_order_id, 'order', $order_id, $customer_id );
		}


	}





}