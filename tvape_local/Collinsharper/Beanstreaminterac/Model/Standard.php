<?php
/**
  
 */
class Collinsharper_Beanstreaminterac_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    //changing the payment to different from cc payment type and beanstreaminterac payment type
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';
	
	const STATE_PENDING_INTERNETSECURE_PAYMENT = 'pending_beanstreaminterac_payment';
	
    const DATA_CHARSET = 'utf-8';

    protected $_code  = 'beanstreaminterac';
    protected $ApiUrl  = 'https://www.beanstream.com/scripts/process_transaction.asp';
    protected $_redirect  = '';
    protected $_order  = '';
    protected $_orderInfo  = '';
    protected $_formBlockType = 'beanstreaminterac/standard_form';
    protected $_allowCurrencyCode = array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD');
	protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
	protected $_canAuthorize            = false;
	
  //  protected $_allowCurrencyCode = array('CAD');

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_allowCurrencyCode)) {
            return false;
        }
        return true;
    }
	
	public function	getGatewayId() {
		return Mage::getStoreConfig('payment/beanstreaminterac/merchant_gatewayid');
	}
		
	public function	getReturnURL() {
		return Mage::getUrl('interac/standard/success');
	}

	public function	getCancelURL() {
		return Mage::getUrl('interac/standard/cancel');
	}

	private function getComUrl()
	{
		return Mage::getStoreConfig('payment/beanstreaminterac/com_url');
	}

	private function getTestDebug()
	{
		return 	(Mage::getStoreConfig('payment/beanstreaminterac/debug') || Mage::getStoreConfig('payment/beanstreaminterac/test_mode'));

	}

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array('business');

     /**
     * Get beanstreaminterac session namespace
     *
     * @return Collinsharper_Beanstreaminterac_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('beanstreaminterac/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

	
	public function getOrderData()
	{
		if(strstr(Mage::getVersion(),'1.3.'))
		{
			return $this->getQuote();
		}
		else
		{
			return $this->getOrder();
		}	
	}
    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    public function getOrder()
    { 
		if(!isset($this->_orderIncrementId))
		{
			$this->_orderIncrementId = $this->getCheckout()->getLastRealOrderId();
		}
		//mage::log(__FUNCTION__ . " oi ". $this->_orderIncrementId);
		if(!is_object($this->_order))
		{
			$this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->_orderIncrementId);
			$this->_orderInfo = Mage::getModel('sales/order_api')->info($this->_orderIncrementId);
		}		
        return $this->_order;
    }

    /**
     * Using internal pages for input payment data
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return false;
    }

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('beanstreaminterac/standard_form', $name)
            ->setMethod('beanstreaminterac_standard')
            ->setPayment($this->getPayment())
            ->setTemplate('beanstreaminterac/standard/form.phtml');

        return $block;
    }

    /*validate the currency code is avaialable to use for beanstreaminterac or not*/
    public function validate()
    {
		return $this;
        parent::validate();
        $currency_code = $this->getOrderData()->getBaseCurrencyCode();
        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
            Mage::throwException(Mage::helper('beanstreaminterac')->__('Selected currency code ('.$currency_code.') is not compatible with beanstreaminterac'));
        }
		//mage::log("Validate ");
        return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {
		mage::log("on Validate ");
		mage::log(__CLASS__ . __LINE__ );
		mage::log("we have quote Base " . $this->getQuote()->getBaseCurrencyCode());
		mage::log("we have quote " .$this->getQuote()->getCurrencyCode() );
		mage::log("we have order Base " . $this->getOrderData()->getBaseCurrencyCode());
		mage::log("we have order " . $this->getOrderData()->getCurrencyCode());

       return $this;
    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {

    }

    public function canCapture()
    {
        return true;
    }

	
	
    public function getStandardCheckoutFormFields()
    {
		
            $billing = $this->getOrderData()->getBillingAddress();

            $shipping = $this->getOrderData()->getShippingAddress();
		//mage::log(__LINE__."oi". $this->getOrder()->getId()." ok ".$billing->getCountry());
        if(!is_object($shipping) || !$shipping->getFirstname())
        {
            $shipping = $billing;
        }

        $currency_code = $this->getOrderData()->getBaseCurrencyCode();
        
		$countryid=0; 
		$countryid=$billing->getCountry();
      $state_canus=$billing->getRegionCode();
      $state_zone=$billing->getRegionCode();
      if (!$state_canus) 
	  {
		$state_canus = "--";
      }

	     // beanstream uses state only for canada/us.
      $country_delivery_id=null;
      $country_delivery_id=$shipping->getCountry();
      $state_delivery_canus=$shipping->getRegionCode();
      if (!$state_delivery_canus) 
	  {
	    $state_delivery_canus = "--";
      }
	  
      $country_delivery_abbrev=$shipping->getCountry();
		$subamount=0;
		$tax=0;
		$shippingamnt=0;
		$shiptmp=0;
		if(strstr(Mage::getVersion(),'1.3.'))
		{
			  $subamount = ($shipping->getBaseSubtotal()+$billing->getBaseSubtotal())-($shipping->getBaseDiscountAmount()+
                        $billing->getBaseDiscountAmount())-(max(0,$shipping->getBaseGiftcertAmount())+max(0,
            $billing->getBaseGiftcertAmount()));
					// line added for unirgy gift amount
					
				$_shippingTax = $this->getOrderData()->getShippingAddress()->getBaseTaxAmount();
				$_billingTax = $this->getOrderData()->getBillingAddress()->getBaseTaxAmount();
				$tax = sprintf('%.2f', $_shippingTax + $_billingTax);
			$totalArr = $billing->getTotals();
			$shippingamnt = '0.00';
			$shiptmp = sprintf('%.2f', $this->getOrderData()->getShippingAddress()->getBaseShippingAmount());
		}
		else
		{
			
				$subamount=($this->_order->getGrandTotal()-$this->_order->getBaseShippingAmount());
				$tax=0;
				$shiptmp=$this->_order->getBaseShippingAmount();
		
			foreach ($this->_order->getAllItems() as $item) 
			{
				$product = mage::getModel('catalog/product')->load($item->getProductId());
				$taxClassId = $product->getTaxClassId();
				$request = Mage::getSingleton('tax/calculation')->getRateRequest($this->_order->getShippingAddress(), $this->_order->getBillingAddress(), false, $item->getStore());
				$request->setProductClassId($taxClassId);
				$rates = Mage::getSingleton('tax/calculation')->getAppliedRates($request);
				foreach($rates[0]['rates'] as $key => $rate) 
				{
					$tax += (float)$rate['percent']/100 * $item->getRowTotal();
				}
			}
			$subamount-=$tax;
		}
		
		if ($shiptmp >0&& !$this->getOrderData()->getIsVirtual())
		{
		    $shippingamnt = $shiptmp;
		}
	

	  
				$sArr = array(
				'requestType'       => 'BACKEND',
				'merchant_id'       => Mage::getStoreConfig('payment/beanstreaminterac/merchant_id'),
				'paymentMethod'     => 'IO',
                'cavEnabled'     => '0', // We never want to try CAV for Interac
				'trnOrderNumber'    => $this->getCheckout()->getLastRealOrderId(),
				'trnType'           => 'P',  // only P is supported with interac online
				'approvedPage'		=> Mage::getUrl('interac/standard/success',array('_secure' => true)),
				'declinedPage'     	=> Mage::getUrl('interac/standard/cancel',array('_secure' => false)),
				// if you pas these beanstream gets all steamed!
				//      'ioFunded'          => Mage::getUrl('interac/standard/success',array('_secure' => true)),
				//    'ioNonFunded'     	=> Mage::getUrl('interac/standard/cancel',array('_secure' => false)),
				//            'notify_url'        => Mage::getUrl('/standard/ipn'),
				'currency_code'     	=> $currency_code,
				'ordName' 			=> $billing->getFirstname() . ' ' .$billing->getLastname(),
				//           'ordEmailAddress' 	=> $this->getPayment()->getOrder()->getCustomerEmail(),
				'ordEmailAddress' 	=> (strlen($billing->getEmail()) ? $billing->getEmail() : $this->_order->getCustomerEmail()),
				'ordPhoneNumber'		=> $billing->getTelephone(),
				'ordAddress1'		=> $billing->getStreet(1),
				'ordCity'			=> $billing->getCity(),
				'ordProvince'		=> $state_canus,
				'ordPostalCode'		=> $billing->getPostcode(),
				'ordCountry'		=> $billing->getCountry(),
				'shipName' 			=> $shipping->getFirstname() . ' ' .$shipping->getLastname(),
				'shipAddress1'		=> $shipping->getStreet(1) ,
				'shipCity'			=> $shipping->getCity() ,
				'shipProvince' 		=> $state_delivery_canus,
				'shipPostalCode'	=> $shipping->getPostcode() ,
				'shipCountry' 		=> $country_delivery_abbrev ,
				'ordItemPrice' 		=> sprintf("%01.2f",$subamount),
				'ordShippingPrice' 	=> sprintf("%01.2f",$shippingamnt),
				'ordTax1Price' 		=> sprintf("%01.2f",$tax),
				'trnAmount' 		=> sprintf("%01.2f",($subamount + $shippingamnt + $tax)),  //this is ugly
				);
		
	if(Mage::getStoreConfig('payment/beanstreaminterac/store_id'))
	{
	  $sArr['username']	= Mage::getStoreConfig('payment/beanstreaminterac/store_id');
	}

	if(Mage::getStoreConfig('payment/beanstreaminterac/password'))
	{
	  $sArr['password'] = Mage::getStoreConfig('payment/beanstreaminterac/password');
	}
	  
        $sReq = '';
        $rArr = array();
        foreach ($sArr as $k=>$v) {
            /*
            replacing & char with and. otherwise it will break the post
            */
            $value =  str_replace("&","and",$v);
            $rArr[$k] =  $value;
            $sReq .= '&'.$k.'='.$value;
        }

        if($this->getTestDebug())
        {
            mage::log("we have " . print_r($rArr,1));
        }

        return $rArr;
    }

    public function getBsRedirectCode()
    {
       
		$url="https://www.beanstream.com/scripts/process_transaction.asp"; 
		$arr = $this->getStandardCheckoutFormFields() ;
		$fields = '';
		foreach($arr as $k=>$v)
		{
		  $fields .= $k.'='.$v.'&';
		}
	    $fields = trim($fields,'&');

  
	    $ch = curl_init();
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $qresult = curl_exec($ch); 
        if($this->getTestDebug())
        {
            mage::log("sent to beanstream". urldecode($fields));
            mage::log("return from beanstream". urldecode($qresult));
        }

        $response = array();
        $res = explode( '&', $qresult);
        foreach($res as $v)
        {
          $d = explode('=',$v);

          $response[$d[0]] = $d[1];
        }

        if($this->getTestDebug())
        {
            mage::log("interac return " . print_r(	$response,1));
        }

        if (curl_errno($ch)) {
			// big error here
			//mage::log("we had a curl error". urldecode($qresult));
        } else {
            curl_close($ch);
		}		
        // return $url;
		if(isset($response['responseType']) && $response['responseType'] == 'R')
		{
			return urldecode($response['pageContents']);
		} 
			//mage::log(__FILE__ . " something failed with beanstream interac payment  ". $fields . print_r($response,1)); 
		return "Your account has not been charges. Something failed in the payment processing. Please contact customer support";
    }

	
    public function getRedirectCode()
	{
		$this->_redirect = Mage::getUrl('interac/standard/redirect');
		return $this->_redirect;
	}
    
	public function getOrderPlaceRedirectUrl()
    {
		$this->_redirect = $this->getRedirectCode();
			//mage::log(__FUNCTION__ ."  oi " . $this->_redirect );
		if(!strlen($this->_redirect))
		{
				Mage::throwException(Mage::helper('beanstreaminterac')->__('Could not get redirect code.'));
				//mage::log(__FUNCTION__ ." Failed to get interac redirect code");
		}
          return Mage::getUrl('interac/standard/redirect', array('_secure' => true));
    }


    public function getbeanstreaminteracUrl()
    {  
         return $this->ApiUrl;
    }



    public function isInitializeNeeded()
    {
        return true;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $state = Collinsharper_Beanstreaminterac_Model_Order::STATE_PENDING_INTERNETSECURE_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus(Mage::getSingleton('sales/order_config')->getStateDefaultStatus($state));
		$stateObject->setIsNotified(false);
    }

	
	
	
	public function successSubmit() {
		$order = mage::getModel('sales/order')->loadByIncrementId($this->getSuccessFormData('trnOrderNumber')); 		
		
		if($order->getId())
        {
			$order->sendNewOrderEmail();
			  //when verified need to convert order into invoice
            $id = $this->getSuccessFormData('trnOrderNumber');
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($id);
            $trnApproved = $this->getSuccessFormData('trnApproved');
            $trnId = $this->getSuccessFormData('trnId');
            $messageText = $this->getSuccessFormData('messageText');
            $ioConfCode = $this->getSuccessFormData('ioConfCode');
            $ioInstName = $this->getSuccessFormData('ioInstName');
            $trnAmount = $this->getSuccessFormData('trnAmount');
            $trnDate = $this->getSuccessFormData('trnDate');
            $comment = '';
            $comment = "
                -Transaction Id: ".(strlen($trnId) !== false ? $trnId : '')."
                -Order Number: ".(strlen($id) !== false ? $id : '')."
                -Amount: ".(strlen($trnAmount) !== false ? $trnAmount : '')."
                -Currency Type: CAD
                -Financial Institution Confirmation Code: ".(strlen($ioConfCode) !== false ? $ioConfCode : '')."
                -Financial Institution Name: ".(strlen($ioInstName) !== false ? $ioInstName : '')."
                -Response message text: ".(strlen($messageText) !== false ? $messageText : '')."
                -Transaction Date: ".(strlen($trnDate) !== false ? $trnDate : '')."
    ";


             $newOrderStatus = 'processing';

			/*
			if payer_status=verified ==> transaction in sale mode
			if transactin in sale mode, we need to create an invoice
			otherwise transaction in authorization mode
			*/
			   if (!$order->canInvoice())
               {
				   //when order cannot create invoice, need to have some logic to take care
				   $order->addStatusToHistory(
						$order->getStatus(), // keep order status/state
						Mage::helper('beanstreaminterac')->__('Error in creating an invoice', true),
						$notified = true
				   );

			   } else {
				   //need to save transaction id
				   $order->getPayment()->setTransactionId($this->getSuccessFormData('trnId'));
				   //need to convert from order into invoice
				   $invoice = $order->prepareInvoice();
				 //  $invoice->register()->pay();
				   $invoice->register()->pay();
				     //$invoice->capture();
				   Mage::getModel('core/resource_transaction')
					   ->addObject($invoice)
					   ->addObject($invoice->getOrder())
					   ->save();
				   $order->setState(
					   $newOrderStatus, $newOrderStatus,
					   Mage::helper('beanstreaminterac')->__('Invoice #%s created', $invoice->getIncrementId()),
					   $notified = true
				   );
				   $order->save();
			   }
			   	 $order->addStatusToHistory(
                    $order->getStatus(),//continue setting current order status
                    Mage::helper('beanstreaminterac')->__('Interac Payment successful, '.$comment)
                );
                $order->save();
		   }
		   else 
		   {
				//mage::log("there's been some kind of error as no order can be found");
		   }
		}

		// this may have been helping is double captures
	

	
	}

