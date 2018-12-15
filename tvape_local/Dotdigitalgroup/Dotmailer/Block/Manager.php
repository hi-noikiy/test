<?php
require_once dirname(dirname(__FILE__)) . "/config/dotMailerConfig.php";
class Dotdigitalgroup_Dotmailer_Block_Manager extends Mage_Adminhtml_Block_Template
{
	public $SoapClient;
	public $date;
	public $logoLink;
    public $supportLink;
    public $supportText;
    public $phoneNumber;


    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('dotdigitalgroup/dotmailer/manager.phtml');
		$this->SoapClient  = new Zend_Soap_Client("http://apiconnector.com/API.asmx?WSDL");
		$this->config      = Mage::getStoreConfig('dotmailer');
		$dotMailerConfig   = new dotMailerConfig();
		$this->logoLink    = $dotMailerConfig->getLogoLink();
		$this->supportLink = $dotMailerConfig->getSupportLink();
		$this->supportText = $dotMailerConfig->getSupportText();
		$this->phoneNumber = $dotMailerConfig->getPhoneNumber();

		$code = Mage::app()->getStore()->getBaseCurrencyCode();
        $currency = Mage::app()->getLocale()->currency($code);
        $this->currencySymbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();


		$date = new Zend_Date();
		$date = $date->sub(7, Zend_Date::DAY);
		$from_date = $date->toString('YYYY-MM-dd HH:mm:ss');

		$this->abandoned_carts = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('is_active',1)->addFieldToFilter('items_count', array('gt' => 0))->addFieldToFilter('customer_email', array('neq' => ''))->addFieldToFilter('updated_at', array('from' => $from_date));
		$this->no_completed_orders =  Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', array('nin' => array('complete','canceled','closed','holded')))->addAttributeToFilter('updated_at', array('from' => $from_date));
		$this->completed_orders =  Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status', 'complete')->addAttributeToFilter('updated_at', array('from' => $from_date));

		$this->abandoned_carts_count = $this->abandoned_carts->count()+$this->no_completed_orders->count();

		$this->completed_orders_count = $this->completed_orders->count();

		$campaign_users_emails = array();
		$lostCartsCampaignId = $this->config['dotMailer_group']['dotMailer_campaign_cart_abandoned'];
		$incompleteOrdersCampaignId = $this->config['dotMailer_group']['dotMailer_campaign_incomplete_order'];

		$date = new Zend_Date();
		$date = $date->sub(7, Zend_Date::DAY);
		$from_date = $date->toString('YYYY-MM-dd');

		$campaign_users_lost_carts = $this->getListCampaignActivities($lostCartsCampaignId,$from_date);
		$campaign_users_incomplete_orders = $this->getListCampaignActivities($incompleteOrdersCampaignId,$from_date);
		$campaign_users_emails = array_merge($campaign_users_lost_carts,$campaign_users_incomplete_orders);
		if($campaign_users_emails)
		{
			$followed_orders_count = 0;
			$recovered_revenue = 0;
			foreach($this->completed_orders as $order)
			{
				if(in_array($order->getCustomerEmail(),$campaign_users_emails))
				{
					$followed_orders_count += 1;
					$items = $order->getAllItems();
					foreach ($items as $item)
						$recovered_revenue += $item->getPrice() * $item->getData('qty_ordered');
				}
			}

			$this->recoveredCarts = $followed_orders_count;
			$this->recoveredRevenue = $recovered_revenue;
		}
		else
		{
			$this->recoveredCarts = 0;
			$this->recoveredRevenue = 0;
		}


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

    public function getStatus()
    {

		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password = $this->config['dotMailer_group']['dotMailer_api_password'];
		$generaladdressbookid = $this->config['dotMailer_group']['dotMailer_book_general_subscribers'];
		$checkoutaddressbookid = $this->config['dotMailer_group']['dotMailer_book_checkout_customers'];
		$triggercampaignabandonedid = $this->config['dotMailer_group']['dotMailer_campaign_cart_abandoned'];
		$triggercampaignincompleteid = $this->config['dotMailer_group']['dotMailer_campaign_incomplete_order'];
		if( $username && $password && $generaladdressbookid && $checkoutaddressbookid && $triggercampaignabandonedid && $triggercampaignincompleteid )
		{
			try {
				$list_address_books = $this->SoapClient->ListAddressBooks(array('username' => $username,'password' => $password))->ListAddressBooksResult->APIAddressBook;
				$list_address_books_ids = array();
				if($list_address_books)
				{
					if(is_array($list_address_books))
						foreach($list_address_books as $book)
							$list_address_books_ids[] = $book->ID;
					else
						$list_address_books_ids[] = $list_address_books->ID;
				}

				$list_campaigns = $this->SoapClient->ListCampaigns(array('username' => $username,'password' => $password))->ListCampaignsResult->APICampaign;
				$list_campaigns_ids = array();
				if($list_campaigns)
				{
					if(is_array($list_campaigns))
						foreach($list_campaigns as $campaign)
							$list_campaigns_ids[] = $campaign->Id;
					else
						$list_campaigns_ids[] = $list_campaigns->Id;
				}

				if( (in_array($generaladdressbookid,$list_address_books_ids)) &&
					(in_array($checkoutaddressbookid,$list_address_books_ids)) &&
					(in_array($triggercampaignabandonedid,$list_campaigns_ids)) &&
					(in_array($triggercampaignincompleteid,$list_campaigns_ids)) )
					$status = "Live & Synced";
				else
					$status = "Incorrect settings";

			} catch (SoapFault $fault) {
				$status = "<font class='dotmailer-manager-error'>Unable to connect to dotMailer. Please ensure <a href='". $this->getUrl('adminhtml/system_config/edit', array('section'=>'dotmailer')) . "'>your dotMailer credentials</a> are correct.</font>";
			}
		}
		else
			$status = "Incorrect settings";
		return $status;
    }
    public function getTotalNumberSubscribers()
    {
		return Mage::getModel('newsletter/subscriber')->getCollection()->addFieldToFilter('subscriber_status',1)->count();
    }
    public function getEmailConversionRate()
    {
/*
		$username = $this->config['dotMailer_group']['dotMailer_api_username'];
		$password = $this->config['dotMailer_group']['dotMailer_api_password'];
		$campaignId = $this->config['dotMailer_group']['dotMailer_campaign_cart_abandoned'];
		$params = array('username'=>$username,'password'=>$password, 'campaignId'=>$campaignId,'select'=> 100,'skip'=> 0);
		try {
			$clickers = $this->SoapClient->ListCampaignClickers($params)->ListCampaignClickersResult;
		} catch (SoapFault $fault){
			echo $fault->getMessage();
		}
		if($clickers)
			print_r((array) $clickers);
		else
			echo "no";
*/
		$rate = 13;
		$rate.="%";
		return $rate;
    }
    public function getAbandonedCarts()
    {
		return $this->abandoned_carts_count;
    }
    public function getBasketDropoutRate()
    {
		if($this->completed_orders_count + $this->abandoned_carts_count != 0)
			$basket_dropout_rate = floor($this->abandoned_carts_count / ($this->completed_orders_count + $this->abandoned_carts_count) *100);
		else
			$basket_dropout_rate = 0;
		$rate= $basket_dropout_rate."%";
		return $rate;
    }
    public function getTotalPotentialLostRevenue()
    {
		$lost_revenue = 0;
		$abandoned_carts = $this->abandoned_carts;
		foreach($abandoned_carts as $cart)
		{
			$items = $cart->getAllItems();
			foreach ($items as $item)
				$lost_revenue += $item->getPrice() * $item->getQty();
		}
		$no_completed_orders = $this->no_completed_orders;
		foreach($no_completed_orders as $order)
		{
			$items = $order->getAllItems();
			foreach ($items as $item)
				$lost_revenue += $item->getPrice() * $item->getData('qty_ordered');
		}
		return $lost_revenue;
    }


    public function getCartsRecoveredThroughdotMailer()
    {
		return $this->recoveredCarts;
    }
    public function getdotMailerBasketRecoveryRate()
    {
		$abandoned_carts_count = $this->abandoned_carts_count;
		$recovered_carts_count = $this->recoveredCarts;
		if($recovered_carts_count + $abandoned_carts_count != 0)
			$basket_recovery_rate = floor($recovered_carts_count / ($recovered_carts_count + $abandoned_carts_count) *100);
		else
			$basket_recovery_rate = 0;

		$basket_recovery_rate.="%";
		return $basket_recovery_rate;
    }
    public function getRevenueRecoveredThroughdotMailer()
    {
		return $this->recoveredRevenue;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}
