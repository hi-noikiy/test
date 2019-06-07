<?php
 class CheckoutApi_ChargePayment_Block_Checkoutjs extends Mage_Core_Block_Template
{
     private function _getQuote()
     {
         return  Mage::getSingleton('checkout/session')->getQuote();
     }

     public  function getPublicKey()
     {
         return $this->getConfigData('publickey');
     }

     public function getAmount()
     {
         return   $this->_getQuote()->getGrandTotal();

     }

     public function getCurrency()
     {
         return   Mage::app()->getStore()->getCurrentCurrencyCode();

     }

    public function getEmailAddress()
    {
        $helper = Mage::helper('customer');
        if($helper->isLoggedIn())    {

            $customer = $helper->getCustomer();
            return  $customer->getEmail();
        }
        return  $this->_getQuote()->getBillingAddress()->getEmail();
    }

    public function getName()
    {   
        $helper = Mage::helper('customer');
        if($helper->isLoggedIn())    {
            $customer = $helper->getCustomer();
            $customerName = $customer->getFirstname() .' '.$customer->getLastname();
            return  $customerName;
        }
        return  $this->_getQuote()->getBillingAddress()->getFirstname(). ' '. $this->_getQuote()->getBillingAddress()->getLastname();
    }

     public function getConfigData($field, $storeId = null)
     {
         if (null === $storeId) {
             $storeId = $this->getStore();
         }
         $path = 'payment/creditcard/'.$field;
         return Mage::getStoreConfig($path, $storeId);
     }

     public function getStoreName()
     {
         return  Mage::app()->getStore()->getName();
     }



     public function isSelected()
     {

        return $this->_getQuote()->getPayment()->getMethod() == 'creditcard';
     }
     
    public function getis3D()
    {
        return $this->getConfigData('card_type');
    }
    
    public function getConvertAmount()
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));
        $currencyDesc = Mage::app()->getStore()->getCurrentCurrencyCode();
        $amount = $Api->valueToDecimal($this->_getQuote()->getGrandTotal(),$currencyDesc);
        
        return  $amount;
    }

    public function getBillingDetails(){
        $billingAddress = $this->_getQuote()->getBillingAddress();
        $street = Mage::helper('customer/address')
            ->convertStreetLines($billingAddress->getStreet(), 2);
        $billingAddressConfig = array(
            'addressLine1'   =>    $street[0],
            'addressLine2'   =>    $street[1],
            'postcode'       =>    $billingAddress->getPostcode(),
            'country'        =>    $billingAddress->getCountry(),
            'city'           =>    $billingAddress->getCity(),
            'phone'          =>    array('number' => $billingAddress->getTelephone()),
        );

        return $billingAddressConfig;
    }
    
    public function getPaymentTokenResult($orderid = null)
    {

        $Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));
        $secretKey = $this->getConfigData('privatekey');
        
        $billingAddress = $this->_getQuote()->getBillingAddress();
        $shippingAddress = $this->_getQuote()->getShippingAddress();
        $orderedItems = $this->_getQuote()->getAllItems();
        $currencyDesc =  Mage::app()->getStore()->getCurrentCurrencyCode();
        $minAmount3D =  $Api->valueToDecimal($this->getConfigData('min_amount'),$currencyDesc);
		$amountCents = $Api->valueToDecimal($this->getAmount(),$currencyDesc);

        $chargeMode = $this->getis3D();
        $chargeModeValue = 1;
        if($chargeMode) {
            if($amountCents > $minAmount3D){
              $chargeModeValue = 2;
            }
        }
        
        $street = Mage::helper('customer/address')
            ->convertStreetLines($shippingAddress->getStreet(), 2);
        $shippingAddressConfig = array(
            'addressLine1'       =>     $street[0],
            'addressLine2'       =>     $street[1],
            'postcode'           =>     $shippingAddress->getPostcode(),
            'country'            =>     $shippingAddress->getCountry(),
            'city'               =>     $shippingAddress->getCity(),
           // 'phone'              =>     array('number' => $shippingAddress->getTelephone())

        );

        $products = array();
        foreach ($orderedItems as $item ) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $products[] = array (
                'name'       =>     $item->getName(),
                'sku'        =>     $item->getSku(),
                'price'      =>     $item->getPrice(),
                'quantity'   =>     $item->getQty(),
                'image'      =>     Mage::helper('catalog/image')->init($product, 'image')->__toString()
            );
        }

        $config = array();
        $config['authorization'] = $secretKey  ;
        $config['mode'] = $this->getConfigData('mode');
        $config['timeout'] = $this->getConfigData('timeout');
        $street = Mage::helper('customer/address')
            ->convertStreetLines($billingAddress->getStreet(), 2);
        $billingAddressConfig = array(
            'addressLine1'   =>    $street[0],
            'addressLine2'   =>    $street[1],
            'postcode'       =>    $billingAddress->getPostcode(),
            'country'        =>    $billingAddress->getCountry(),
            'city'           =>    $billingAddress->getCity(),
            'phone'          =>    array('number' => $billingAddress->getTelephone()),
        );

        $config['postedParam'] = array (
            'trackId'           =>    $orderid,
            'value'             =>    $amountCents,
            'chargeMode'        =>    $chargeModeValue,
            'currency'          =>    $currencyDesc,
            'shippingDetails'   =>    $shippingAddressConfig,
            'products'          =>    $products,
            'metadata'          =>   array(
                                        'server'  => Mage::helper('core/http')->getHttpUserAgent(),
                                        'quoteId' => $this->_getQuote()->getId()

            )
        );

        if($this->getConfigData('payment_action') == 'authorize' ) {
            $config['postedParam']['autoCapture']  = CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH;
            $config['postedParam']['autoCapTime']  = 0;
        } else {
            $config['postedParam']['autoCapture']  = CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE;
            $config['postedParam']['autoCapTime']  = $this->getConfigData('auto_capture_time');
        }
        
        $paymentTokenCharge = $Api->getPaymentToken($config);
        if ($Api->getExceptionState()->hasError()){
          Mage::log($Api->getExceptionState()->getErrorMessage(), null, 'ckoerror.log');
          Mage::log( $config,Zend_Log::DEBUG,'ckopaytoken.log');
        }
        $paymentTokenReturn    =   array(
                                    'success'  => false,
                                    'token'   => '',
                                    'message' => '',
                                    'value' => $amountCents
                                  );
        $paymentToken = '';
        if($paymentTokenCharge->isValid()){
            $paymentToken = $paymentTokenCharge->getId();
            $paymentTokenReturn['token'] = $paymentToken ;
            $paymentTokenReturn['success'] = true;
        }else {
            //$paymentTokenCharge->printError();
        }

        if(!$paymentToken) {
            $paymentTokenReturn['success'] = false;
            if($paymentTokenCharge->getEventId()) {
                $eventCode = $paymentTokenCharge->getEventId();
            }else {
                $eventCode = $paymentTokenCharge->getErrorCode();
            }
            $paymentTokenReturn['message'] = Mage::helper('payment')->__( $paymentTokenCharge->getExceptionState()->getErrorMessage().
                ' ( '.$eventCode.')');
        }

        return $paymentTokenReturn;

    }
}