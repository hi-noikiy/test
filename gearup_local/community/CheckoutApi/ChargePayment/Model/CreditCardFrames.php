<?php

/**
 * Class for CreditCardJs payment method
 *
 * Class CheckoutApi_ChargePayment_Model_CreditCardJs
 *
 */
class CheckoutApi_ChargePayment_Model_CreditCardFrames extends CheckoutApi_ChargePayment_Model_Checkout
{
    protected $_code            = CheckoutApi_ChargePayment_Helper_Data::CODE_CREDIT_CARD_FRAMES;
    protected $_canUseInternal  = false;

    protected $_formBlockType = 'chargepayment/form_checkoutApiFrames';
    protected $_infoBlockType = 'chargepayment/info_checkoutApiFrames';

    const RENDER_MODE           = 2;
    const RENDER_NAMESPACE      = 'CheckoutIntegration';
    const CARD_FORM_MODE        = 'cardTokenisation';

    const PAYMENT_MODE_MIXED            = 'mixed';
    const PAYMENT_MODE_CARD             = 'cards';
    const PAYMENT_MODE_LOCAL_PAYMENT    = 'localpayments';

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info   = $this->getInfoInstance()
            ->setCheckoutApiCardId('')
            ->setPoNumber($data->getSaveCardCheck());

        $result = $this->_getSavedCartDataFromPost($data);

        if (!empty($result)) {
            $info->setCcType($result['cc_type']);
            $info->setCheckoutApiCardId($result['checkout_api_card_id']);
            $info->setCcCid($result['cc_id']);
        }

        return $this;
    }

    protected function _getSavedCartDataFromPost($data) {
        $savedCard  = $data->getCustomerCard();

        /* If non saved card */
        if (empty($savedCard) || $savedCard === 'new_card' || strpos($savedCard, 'checkoutapiframes') !== false) {
            return array();
        }

        $customerId = $this->getCustomerId();

        /* If user not logged */
        if (empty($customerId)) {
            return array();
        }

        $cardModel  = Mage::getModel('chargepayment/customerCard');
        $collection = $cardModel->getCustomerCardList($customerId);

        /* If user not have saved cards */
        if (!$collection->count()) {
            return array();
        }

        $trueData       = false;
        $customerCard   = array();

        foreach($collection as $entity) {
            $secret = $cardModel->getCardSecret($entity->getId(), $entity->getCardNumber(), $entity->getCardType());

            if ($savedCard === $secret) {
                $trueData = true;
                $customerCard = $entity;
                break;
            }
        }

        if (!$trueData) {
            Mage::throwException(Mage::helper('chargepayment')->__('Please check your card data.'));
        }

        $result['checkout_api_card_id'] = $customerCard->getCardId();
        $result['cc_id']                = $data->getCcId();

        return $result;
    }

    /**
     * Return to checkout page
     *
     * @return bool|string
     *
     */
    public function getCheckoutRedirectUrl() { 
        $controllerName     = (string)Mage::app()->getFrontController()->getRequest()->getControllerName();

        if ($controllerName === 'onepage' || $controllerName === 'index') {
            return false;
        }

        $requestData        = Mage::app()->getRequest()->getParam('payment');
        $cardToken          = !empty($requestData['checkout_card_token']) ? $requestData['checkout_card_token'] : null;
        $lpRedirectUrl      = !empty($requestData['lp_redirect_url']) ? $requestData['lp_redirect_url'] : NULL;
        $session            = Mage::getSingleton('chargepayment/session_quote');

        if (!is_null($cardToken) || !is_null($lpRedirectUrl)) {
            return false;
        }

        $params['method'] = $this->_code;

        $session->setJsCheckoutApiParams($params);

        return Mage::helper('checkout/url')->getCheckoutUrl();
    }

    /**
     * Return redirect url for 3d and local payments
     *
     * @return bool
     */
    public function getOrderPlaceRedirectUrl() { 
        $session    = Mage::getSingleton('chargepayment/session_quote');
        $isLocal    = $session->getIsLocalPayment();
        $lpUrl      = $session->getLpRedirectUrl();
        $is3d       = $session->getIs3d();
        $is3dUrl    = $session->getPaymentRedirectUrl();
        $helper     = Mage::helper('chargepayment');

        $session
            ->setIsLocalPayment(false)
            ->setLpRedirectUrl(false)
            ->setIs3d(false)
            ->setPaymentRedirectUrl(false);

        if ($isLocal) {
            $helper->setOrderPendingPayment();

            return $lpUrl;
        }

        if ($is3d && $is3dUrl) {
            $helper->setOrderPendingPayment();

            return $is3dUrl;
        }

        return false;
    }

    /**
     * Create Payment Token
     *
     * @return array
     *
     * @version 20160203
     */
    public function getPaymentToken($orderId=null) {
        $Api                = CheckoutApi_Api::getApi(array('mode' => $this->getEndpointMode()));
        $isCurrentCurrency  = $this->getIsUseCurrentCurrency();
        $price              = $isCurrentCurrency ? $this->_getQuote()->getGrandTotal() : $this->_getQuote()->getBaseGrandTotal();
        $priceCode          = $isCurrentCurrency ? $this->getCurrencyCode() : Mage::app()->getStore()->getBaseCurrencyCode();

		// does not create charge on checkout.com if amount is 0
        if (empty($price)) {
            return array();
        }
		
        $amount     = $Api->valueToDecimal($price, $priceCode);
        $config     = $this->_getCharge($amount);

        if(isset($orderId)){
            $config['postedParam']['trackId'] = $orderId;
        }

        $paymentTokenCharge = $Api->getPaymentToken($config);

        if ($Api->getExceptionState()->hasError()) {
            Mage::log($Api->getExceptionState()->getErrorMessage(), null, $this->_code.'.log');
            Mage::log($Api->getExceptionState(), null, $this->_code.'.log');

            return array(
                'success' => false,
                'token'   => '',
                'message' => $Api->getExceptionState()->getErrorMessage()
            );
        }

        $paymentTokenReturn     = array(
            'success' => false,
            'token'   => '',
            'message' => ''
        );

        if($paymentTokenCharge->isValid()){
            $paymentToken                   = $paymentTokenCharge->getId();
            $paymentTokenReturn['token']    = $paymentToken ;
            $paymentTokenReturn['success']  = true;

            $paymentTokenReturn['customerEmail']    = $config['postedParam']['email'];
            $paymentTokenReturn['customerName']     = $config['postedParam']['customerName'];
            $paymentTokenReturn['value']            = $amount;
            $paymentTokenReturn['currency']         = $priceCode;

            Mage::getSingleton('chargepayment/session_quote')->addPaymentToken($paymentToken);
        }else {
            if($paymentTokenCharge->getEventId()) {
                $eventCode = $paymentTokenCharge->getEventId();
            }else {
                $eventCode = $paymentTokenCharge->getErrorCode();
            }
            $paymentTokenReturn['message'] = Mage::helper('payment')->__( $paymentTokenCharge->getExceptionState()->getErrorMessage().
                ' ( '.$eventCode.')');

            Mage::logException($paymentTokenReturn['message']);
        }

        return $paymentTokenReturn;
    }

    /**
     * Return Quote from session
     *
     * @param null $quoteId
     * @return mixed
     *
     * @version 20160202
     */
    private function _getQuote($quoteId = null) {
        $quoteId = (int)$quoteId;
        if (!empty($quoteId)) {
            return Mage::getModel('sales/quote')->load($quoteId);
        }
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Get Public Shared Key
     *
     * @return mixed
     *
     * @version 20160407
     */
    public function getPublicKeyWebHook() {
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'publickey_web');
    }

    /**
     * Get Secret Key
     *
     * @return mixed
     *
     * @version 20161910
     */
    public function getSecretKey() {
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'secretkey');
    }

    /**
     * Get Endpoint Mode
     *
     * @return mixed
     *
     * @version 20161910
     */
    public function getMode() {
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'mode');
    }


    /**
     * Return true if is 3D
     *
     * @return bool
     *
     * @version 20160202
     */
    public function getIs3D() {
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'is_3d');
    }

    /**
     * Return the timeout value for a request to the gateway.
     *
     * @return mixed
     *
     * @version 20160203
     */
    public function getTimeout() {
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'timeout');
    }

    /**
     * Validate payment method information object
     *
     * @return Mage_Payment_Model_Abstract
     *
     * @version 20160203
     */
    public function validate() {
        return $this;
    }

    /**
     * For authorize
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Mage_Core_Exception
     *
     * @version 20160204
     */
    public function authorize(Varien_Object $payment, $amount) {
		// does not create charge on checkout.com if amount is 0
        if (empty($amount)) {
            return $this;
        }

        $requestData        = Mage::app()->getRequest()->getParam('payment');
        $session            = Mage::getSingleton('chargepayment/session_quote');

        if(strpos($requestData['customer_card'], 'checkoutapiframes') !== false){

            $result = $this->createLpCharge($requestData, $payment);

            if($result->isValid()) {
                $localPayment = $result['localPayment'];
                $redirectUrl = $localPayment['paymentUrl'];
                $entityId = $result->getId();

                if(!$redirectUrl){
                    $errorMessage = 'An error has occured, Please verify your payment details.';
                    Mage::log('Empty redirect url', null, $this->_code.'.log');
                    Mage::throwException($errorMessage);
                }

                /* is 3D payment */
                if ($redirectUrl && $entityId) {
                    $payment->setAdditionalInformation('payment_token', $entityId);
                    $payment->setAdditionalInformation('payment_token_url', $redirectUrl);
                    $payment->setTransactionId($entityId);

                    $session->addPaymentToken($entityId);
                    $session
                        ->setIs3d(true)
                        ->setLpRedirectUrl($redirectUrl)
                        ->setIsLocalPayment(true)
                        ->setEndpointMode($this->getEndpointMode())
                        ->setSecretKey($this->_getSecretKey())
                        ->setNewOrderStatus($this->getNewOrderStatus())
                    ;
                } 

                return $this;
            } else {

                $errors             = $result->toArray();

                if (!empty($errors['errorCode'])) {
                    $responseCode       = (int)$errors['errorCode'];
                    $responseMessage    = (string)$errors['message'];
                    $errorMessage       = "Error Code - {$responseCode}. Message - {$responseMessage}.";
                } else {
                    $errorMessage = Mage::helper('chargepayment')->__('Authorize action is not available.');
                }

                Mage::log($errorMessage, null, $this->_code.'.log');
                Mage::throwException($errorMessage);

            }

        }

        $isCurrentCurrency  = $this->getIsUseCurrentCurrency();
        $quoteId            = null;
        $order              = $payment->getOrder();

        /* Local Payment */
        $lpRedirectUrl  = !empty($requestData['lp_redirect_url']) ? $requestData['lp_redirect_url'] : NULL;
        $lpName         = !empty($requestData['lp_name']) ? $requestData['lp_name'] : NULL;
        
        /* Normal Payment */
        $cardToken = $payment->getData('cc_type');

        if(empty($cardToken)){
            $cardToken = !empty($requestData['checkout_card_token']) ? $requestData['checkout_card_token'] : NULL;

            if(empty($cardToken)){
                $checkoutApiCardId = $payment->getCheckoutApiCardId();

                if(is_null($checkoutApiCardId)){
                    Mage::throwException(Mage::helper('chargepayment')->__('Invalid cardId'));
                    Mage::log('Empty Card Id', null, $this->_code.'.log');
                }
            }
        }else {
            $quoteId = $payment->getData('cc_owner');
        }

        $isDebug = $this->isDebug();

        if (empty($cardToken) && empty($checkoutApiCardId)) {
            Mage::throwException(Mage::helper('chargepayment')->__('Authorize action is not available.'));
            Mage::log('Empty Card Token or cardId', null, $this->_code.'.log');
        }

        $price              = $isCurrentCurrency ? $this->_getQuote($quoteId)->getGrandTotal() : $this->_getQuote($quoteId)->getBaseGrandTotal();
        $priceCode          = $isCurrentCurrency ? $this->getCurrencyCode() : Mage::app()->getStore()->getBaseCurrencyCode();

        $Api    = CheckoutApi_Api::getApi(array('mode' => $this->getEndpointMode()));
        $amount = $Api->valueToDecimal($price, $priceCode);
        $config = $this->_getCharge($amount, $quoteId);

        $config['postedParam']['trackId']   = $payment->getOrder()->getIncrementId();

        if(isset($cardToken)){
            $config['postedParam']['cardToken'] = $cardToken;
        }

        if(isset($checkoutApiCardId)){
            $config['postedParam']['cardId'] = $checkoutApiCardId;
            $config['postedParam']['cvv']    = $payment->getCcCid();
        }

        $result         = $Api->createCharge($config);

        if (is_object($result) && method_exists($result, 'toArray')) {
            Mage::log($result->toArray(), null, $this->_code.'.log');
        }

        if ($Api->getExceptionState()->hasError()) {
            Mage::log($Api->getExceptionState()->getErrorMessage(), null, $this->_code.'.log');
            Mage::log($Api->getExceptionState(), null, $this->_code.'.log');
            $errorMessage = Mage::helper('chargepayment')->__('Your payment was not completed.'. $Api->getExceptionState()->getErrorMessage().' and try again or contact customer support.');
            Mage::throwException($errorMessage);
        }

        $toValidate = array(
            'currency' => $priceCode,
            'value'    =>  $Api->valueToDecimal($price, $priceCode),
        );

        $validateRequest = $Api->validateRequest($toValidate,$result);

        if($result->isValid()) {
            if ($this->_responseValidation($result)) {
                $redirectUrl    = $result->getRedirectUrl();
                $entityId       = $result->getId();

                /* is 3D payment */
                if ($redirectUrl && $entityId) {
                    $payment->setAdditionalInformation('payment_token', $entityId);
                    $payment->setAdditionalInformation('payment_token_url', $redirectUrl);
                    $payment->setTransactionId($entityId);

                    $session->addPaymentToken($entityId);
                    $session
                        ->setIs3d(true)
                        ->setPaymentRedirectUrl($redirectUrl)
                        ->setEndpointMode($this->getEndpointMode())
                        ->setSecretKey($this->_getSecretKey())
                        ->setNewOrderStatus($this->getNewOrderStatus())
                    ;
                } else {
                    /* Save customer's card Id */
                    Mage::getModel('chargepayment/customerCard')->saveCard($payment, $result);

                    $payment->setTransactionId($entityId);
                    $payment->setIsTransactionClosed(0);
                    $payment->setAdditionalInformation('use_current_currency', $isCurrentCurrency);

                    if($validateRequest['status']!== 1 && (int)$result->getResponseCode() !== CheckoutApi_ChargePayment_Model_Checkout::CHECKOUT_API_RESPONSE_CODE_APPROVED ){
                        $order->addStatusHistoryComment('Suspected fraud - Please verify amount and quantity.', false);
                        $payment->setIsFraudDetected(true);
                    } else {
                        $payment->setState('pending');
                    }

                    $session->setIs3d(false);
                }
            } 
        } else {
            if ($isDebug) {
                /* Authorize processing error response. */
                $errors             = $result->toArray();

                if (!empty($errors['errorCode'])) {
                    $responseCode       = (int)$errors['errorCode'];
                    $responseMessage    = (string)$errors['message'];
                    $errorMessage       = "Error Code - {$responseCode}. Message - {$responseMessage}.";
                } else {
                    $errorMessage = Mage::helper('chargepayment')->__('Authorize action is not available.');
                }
            } else {
                $errorMessage = Mage::helper('chargepayment')->__('Authorize action is not available.');
            }

            Mage::throwException($errorMessage);
            Mage::log($result->printError(), null, $this->_code.'.log');
        }

        return $this;
    }

    /**
     * Return base data for charge
     *
     * @param null $amount
     * @param null $quoteId
     * @return array
     *
     * @version 20160204
     */
    private function _getCharge($amount = null, $quoteId = null) {
        $secretKey          = $this->_getSecretKey();
        $isCurrentCurrency  = $this->getIsUseCurrentCurrency();
        $quote              = $this->_getQuote($quoteId);

        $billingAddress     = $quote->getBillingAddress();
        $shippingAddress    = $quote->getShippingAddress();
        $orderedItems       = $quote->getAllItems();
        $currencyDesc       = $isCurrentCurrency ? $this->getCurrencyCode() : Mage::app()->getStore()->getBaseCurrencyCode();
        $amountCents        = $amount;
        $chargeMode         = $this->getIs3D();
        $shippingCost       = $quote->getShippingAddress()->getShippingAmount();


        $street = Mage::helper('customer/address')
            ->convertStreetLines($shippingAddress->getStreet(), 2);

        $billingAddressConfig = array (
            'addressLine1'  => $street[0],
            'addressLine2'  => $street[1],
            'postcode'      => $billingAddress->getPostcode(),
            'country'       => $billingAddress->getCountry(),
            'city'          => $billingAddress->getCity(),
            'state'         => $billingAddress->getRegion(),
            'phone'         => array('number' => $billingAddress->getTelephone())
        );

        $billingPhoneNumber = $billingAddress->getTelephone();

        if (!empty($billingPhoneNumber)) {
            $billingAddressConfig['phone'] = array('number' => $billingPhoneNumber);
        }

        $shippingAddressConfig = array(
            'recipientName'      => $shippingAddress->getName(),
            'addressLine1'       => $street[0],
            'addressLine2'       => $street[1],
            'postcode'           => $shippingAddress->getPostcode(),
            'country'            => $shippingAddress->getCountry(),
            'city'               => $shippingAddress->getCity(),
            'state'              => $shippingAddress->getCity()
        );

        $phoneNumber = $shippingAddress->getTelephone();

        if (!empty($phoneNumber)) {
            $shippingAddressConfig['phone'] = array('number' => $phoneNumber);
        }

        $products = array();

        foreach ($orderedItems as $item) {
            $product        = Mage::getModel('catalog/product')->load($item->getProductId());
            $productPrice   = $item->getPrice();
            $productPrice   = is_null($productPrice) || empty($productPrice) ? 0 : $productPrice;
            $productImage   = $product->getImage();

            $products[] = array (
                'name'       => $item->getName(),
                'sku'        => $item->getSku(),
                'price'      => $productPrice,
                'quantity'   => $item->getQty(),
                'image'      => $productImage != 'no_selection' && !is_null($productImage) ? Mage::helper('catalog/image')->init($product , 'image')->__toString() : '',
                'shippingCost' => $shippingCost
            );
        }

        $config                     = array();
        $config['authorization']    = $secretKey;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $config['postedParam'] = array (
            'trackId'           => NULL,
            'customerName'      => $billingAddress->getName(),
            'email'             => Mage::helper('chargepayment')->getCustomerEmail($quoteId),
            'value'             => $amountCents,
            'chargeMode'        => $chargeMode,
            'currency'          => $currencyDesc,
            'billingDetails'    => $billingAddressConfig,
            'shippingDetails'   => $shippingAddressConfig,
            // 'products'          => $products,
            'customerIp'        => $ip,
            'metadata'          => array(
                'server'            => Mage::helper('core/http')->getHttpUserAgent(),
                'quoteId'           => $quote->getId(),
                'magento_version'   => Mage::getVersion(),
                'plugin_version'    => Mage::helper('chargepayment')->getExtensionVersion(),
                'lib_version'       => CheckoutApi_Client_Constant::LIB_VERSION,
                'integration_type'  => 'FramesJs',
                'time'              => Mage::getModel('core/date')->date('Y-m-d H:i:s')
            ),

        );

        $autoCapture = 'n';

        if ($this->getAutoCapture() ==1){
            $autoCapture = 'y';
        }

        $config['postedParam']['autoCapture']  = $autoCapture;
        $config['postedParam']['autoCapTime']  = $this->getAutoCapTime();

        return $config;
    }

    public function getAutoCapTime(){
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'autoCapTime');
    }

    public function getAutoCapture(){
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'autoCapture');
    }

    public function getTheme(){
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'theme');
    }

    public function getCustomerId() { 
        if (Mage::app()->getStore()->isAdmin()) {
            $customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomerId();
        } else {
            $customerId = Mage::getModel('checkout/cart')->getQuote()->getCustomerId();
        }

        if(is_null($customerId)){
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        }

        return $customerId ? $customerId : false;
    }

    public function getSaveCardSetting(){
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'saveCard');
    }

    public function getCvvVerification() {
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'cvvVerification');
    }

    public function getIncludeApm(){
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'includeApm');
    }

    public function getLocalPayment(){
        $paymentToken = $this->getPaymentToken();
        $result = $this->getLocalPaymentProvider($paymentToken['token']);
        $alternativePayment=[];

        foreach ((array)$result as &$value) { 
            $alternativePayment[]['lpName'] = $value['name'];
        }

        return $alternativePayment;        
    }

    public function getPublicKey() {
        return Mage::helper('chargepayment')->getConfigData($this->_code, 'publickey');
    }

    public function getLocalPaymentProvider($paymentToken){
        $Api = CheckoutApi_Api::getApi(array('mode' => $this->getEndpointMode()));
        $paymentToken = array('paymentToken' => $paymentToken, 'authorization' => $this->getPublicKey());
        $result = $Api->getLocalPaymentProviderByPayTok($paymentToken);
        $data = $result->getData();

        foreach ((array)$data as &$value) { 
            return $value;
        }
    }

     /**
    *
    * Get Bank info for Ideal
    *
    **/
    public function getLocalPaymentInformation($lpId){ 
        $secretKey = $this->getSecretKey();
        $mode = $this->getEndpointMode();
        $url = "https://sandbox.checkout.com/api2/v2/lookups/localpayments/{$lpId}/tags/issuerid";
        
        if($mode == 'live'){
            $url = "https://api2.checkout.com/v2/lookups/localpayments/{$lpId}/tags/issuerid";
        }


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER=>  false,
            CURLOPT_HTTPHEADER => array(
              "authorization: ".$secretKey,
              "cache-control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
          WC_Checkout_Non_Pci::log("cURL Error #:" . $err);
        } else {

            $test = json_decode($response);

            foreach ((array)$test as &$value) { 
                foreach ($value as $i=>$item){
                    foreach ($item as  $is=>$items) {
                        return $item->values;
                    }
                }
            }
        }
    }

    public function createLpCharge($requestData, $payment){

        $orderId = $payment->getOrder()->getIncrementId();
        $paymentToken = $this->getPaymentToken($orderId);

        $string = $requestData['customer_card'];
        $last_word_start = strrpos($string, '-') + 1; // +1 so we don't include the space in our result
        $apmName = substr($string, $last_word_start); 

        $secretKey = $this->getSecretKey();
        $localpayment = $this->getLocalPaymentProvider($paymentToken['token']);

        foreach ($localpayment as $i=>$item) {
           $lpName = strtolower(preg_replace('/\s+/', '', $item['name']));
           if($lpName == $apmName){
                $lppId = $item['id'];
           }
        }

        $config = array();
        $config['authorization']    = $secretKey;
        $config['postedParam']['email'] = $paymentToken['customerEmail'];
        $config['postedParam']['paymentToken'] = $paymentToken['token'];

        if($requestData['customer_card'] == 'checkoutapiframes-apm-ideal'){
            $config['postedParam']['localPayment'] = array(
                                        "lppId" => $lppId,
                                        "userData" => array(
                                                        "issuerId" => $requestData['cko-lp-issuerId']
                                        )
            );
        } elseif($requestData['customer_card'] == 'checkoutapiframes-apm-boleto'){
            $config['postedParam']['localPayment'] = array(
                                        "lppId" => $lppId,
                                        "userData" => array(
                                                        "birthDate" => $requestData['cko-lp-boletoDate'],
                                                        "cpf" => $requestData['cko-lp-cpf'],
                                                        "customerName" => $requestData['cko-lp-custName']
                                        )
            );
        } elseif($requestData['customer_card'] == 'checkoutapiframes-apm-qiwi'){

            $config['postedParam']['localPayment'] = array(
                                        "lppId" => $lppId,
                                        "userData" => array(
                                                        "walletId" => $requestData['cko-lp-walletId'],
                                        )
            );
        } else {
            $config['postedParam']['localPayment'] = array(
                                        "lppId" => $lppId,
            );
        }


        $Api = CheckoutApi_Api::getApi(array('mode' => $this->getEndpointMode()));
        $result = $Api->createLocalPaymentCharge($config);

        return $result; 

    }




}