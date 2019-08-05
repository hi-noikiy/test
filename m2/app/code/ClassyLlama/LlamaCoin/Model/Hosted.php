<?php

namespace ClassyLlama\LlamaCoin\Model;

class Hosted extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'classyllama_llamacoin';
    const METHOD_CODE = 'optimal_hosted';
    const LOG_FILE_NAME = 'optimal_error.log';

    protected $_code                    = self::CODE;
    protected $_canSaveCc               = false;
    protected $_canAuthorize            = true;
    protected $_canVoid                 = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_isGateway               = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    //protected $_formBlockType   = 'ClassyLlama\LlamaCoin\Block\Form\Creditcard';
    protected $_infoBlockType   = 'ClassyLlama\LlamaCoin\Block\Info\Creditcard';
    
   
    protected $_scopeConfig;
    protected $_helper;
    protected $_customerSession;
    protected $_customer;
    protected $_sessionQuote;
    protected $_hostedClient;
    protected $_quoteFactory;
    protected $_checkoutSession;
    protected $_onepage;
    protected $_responseFactory;
    protected $_messageManager;
    protected $_creditcard;
    protected $_storeManager;
    protected $_serializer;
    protected $_order;
    
    /**
     * 
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Checkout\Model\Type\Onepage $onepage
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \ClassyLlama\LlamaCoin\Helper\Data $helper
     * @param \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard
     * @param \ClassyLlama\LlamaCoin\Model\Hosted\Client $hostedClient
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,    
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,     
        \Magento\Checkout\Model\Type\Onepage $onepage,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,    
        \Magento\Framework\App\ResponseFactory $responseFactory,    
        \Magento\Framework\Message\ManagerInterface $messageManager,  
        \Magento\Framework\Serialize\SerializerInterface $serializer,    
        \ClassyLlama\LlamaCoin\Helper\Data $helper,   
        \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard,    
        \ClassyLlama\LlamaCoin\Model\Hosted\Client $hostedClient,    
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []    
        ) {
            parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,        
            $resource,
            $resourceCollection,
            $data
        );
            $this->_customerSession = $customerSession;
            $this->_checkoutSession = $checkoutSession;
            $this->_customer = $customer;
            $this->_responseFactory = $responseFactory;
            $this->_sessionQuote = $sessionQuote;
            $this->_hostedClient = $hostedClient;
            $this->_scopeConfig = $scopeConfig;
            $this->_helper = $helper;
            $this->_order = $order;
            $this->_creditcard = $creditcard;
            $this->_onepage = $onepage;
            $this->_serializer = $serializer;
            $this->_quoteFactory = $quoteFactory;
            $this->_messageManager = $messageManager;
            $this->_storeManager = $storeManagerInterface;
            
    }
    
    public function isGateway()
    {
        return $this->_isGateway;
    }

    public function canRefund()
    {
        return $this->_canRefund;
    }

    public function canVoid()
    {
        return $this->_canVoid;
    }

    public function canCapturePartial()
    {
        return $this->_canCapturePartial;
    }

    public function canAuthorize()
    {
        return $this->_canAuthorize;
    }

    public function canCapture()
    {
        return $this->_canCapture;
    }
    
    /**
     * 
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param type $transactionId
     * @param type $transactionType
     * @param array $transactionDetails
     * @param array $transactionAdditionalInfo
     * @param type $message
     * @return type
     */
    protected function _addTransaction(\Magento\Sales\Model\Order\Payment $payment,
            $transactionId,
            $transactionType,
            array $transactionDetails = array(),
            array $transactionAdditionalInfo = array(),
            $message = false
    ) {
        $payment->setTransactionId($transactionId);
        $payment->resetTransactionAdditionalInfo();
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $transaction = $payment->addTransaction($transactionType, null, false , $message);
        foreach ($transactionDetails as $key => $value) {
            $payment->unsetData($key);
        }
        $payment->unsLastTransId();

        /**
         * Its for self using
         */
        $transaction->setMessage($message);

        return $transaction;
    }
    
    /**
     * 
     * @param \Magento\Framework\DataObject $data
     * @return $this
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        if (!($data instanceof \Magento\Framework\DataObject)) {
            $data = new \Magento\Framework\DataObject ($data);
        }
        $additionalData = $data->getData(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_object($additionalData)) {
            $additionalData = new \Magento\Framework\DataObject($additionalData ?: []);
        }
        $info = $this->getInfoInstance();

        $profileId = $additionalData->getProfileId();
        
        $info->setOptimalUseInterac($data->getOptimalUseInterac());

        if(isset($profileId) && ($profileId != 0)) {
            $profile = $this->_creditcard->create()->load($profileId, 'entity_id');
            $expiry = explode('/', $profile->getCardExpiration());
            $expiry[1] = 2000 + $expiry[1];
            $info
                ->unsOptimalCreateProfile()
                ->setCcType($profile->getCardNickname())
                ->setCcOwner($profile->getCardHolder())
                ->setCcLast4($profile->getLastFourDigits())
                ->setCcExpMonth($expiry[0])
                ->setCcExpYear($expiry[1])
                ->setCcCidEnc($info->encrypt($data->getCcCid()))
                ->setOptimalProfileId($profileId);
            $info->save();
        } else {
            $info->addData(
            [
                'cc_type' => $additionalData->getCcType(),
                'cc_owner' => $additionalData->getCcOwner(),
                'cc_last_4' => substr($additionalData->getCcNumber(), -4),
                'cc_number' => $additionalData->getCcNumber(),
                'cc_cid' => $additionalData->getCcCid(),
                'cc_exp_month' => $additionalData->getCcExpMonth(),
                'cc_exp_year' => $additionalData->getCcExpYear(),
                'cc_ss_issue' => $additionalData->getCcSsIssue(),
                'cc_ss_start_month' => $additionalData->getCcSsStartMonth(),
                'cc_ss_start_year' => $additionalData->getCcSsStartYear()
            ]
                    
        );
        $info->save();

        }

        return $this;
    }
    
    /**
     * 
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException]
     */
    public function validate()
    {
     
        $skip3d = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/skip3D', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $allowInterac = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/allow_interac', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $info = $this->getInfoInstance();
        $last4 = $info->getData('cc_last4');

        if ((!$skip3d || $allowInterac) && empty($last4)) { // Do not require Credit Card info if NOT skipping 3D verification or when payment method is Interac
            return $this;
        }

        $errorMsg = false;
        $availableTypes = explode(',',$this->getConfigData('cctypes'));


        $optimalProfileId = $info->getOptimalProfileId();
        if ($optimalProfileId) {

            //validate credit card verification number
            if ($errorMsg === false && $this->hasVerification()) {
                $ccId = $info->getCcCid();
                if (!isset($ccId)){
                    $errorMsg = __('Please enter a valid credit card verification number.');
                }
            }

            if($errorMsg){
                throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
            }

        } else {
            parent::validate();
        }

        return $this;
    }
    
    /**
     * Capture Payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $helper = $this->_helper;
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Invalid amount for capture."));
        }

        try {

            $transactionMode = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/payment_action',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($transactionMode == \ClassyLlama\LlamaCoin\Model\Config\Transaction::CAPT_VALUE)
            {
                $result = $this->authorize($payment, $amount);
                return $result;
            }
            $additionalInformation = $payment->getAdditionalInformation();

            if (isset($additionalInformation['transaction'])) {

                $paymentData = $this->_serializer->unserialize($additionalInformation['transaction']);
                $orderData = $this->_serializer->unserialize($additionalInformation['order']);

                $order = $payment->getOrder();
                $payment->setAmount($amount);

                $client = $this->_hostedClient->create(); //Mage::getModel('optimal/hosted_client', array('store_id' => $order->getStoreId()));

                $transactionStatus = $client->retrieveOrder($orderData['id']);

                if ($transactionStatus->transaction->status == 'held')
                {
                    // Prepare api order update
                    $transactionData = array(
                        'transaction' => array(
                            'status' => 'success'
                        )
                    );
                    $response = $client->updateOrder($transactionData, $orderData['id']);
                }

                $data = array(
                    'amount' => (int)$helper->formatAmount($amount),
                    'merchantRefNum' => (string)$paymentData->merchantRefNum
                );

                $response = $client->settleOrder($data, $orderData['id']);
                $orderStatus = $client->retrieveOrder($orderData['id']);
                $transaction = $orderStatus->transaction;

                $associatedTransactions = $transaction->associatedTransactions;

                $payment->setAdditionalInformation('transaction', $this->_serializer->serialize($transaction));

                $order->addStatusHistoryComment(
                    'Trans Type: ' . $response->authType . '<br/>' .
                    'Confirmation Number: ' . $response->confirmationNumber . '<br/>' .
                    'Transaction Amount: ' . $response->amount / 100 . '<br/>'
                );

                return $this;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Transaction information is not properly set."));
            }
        } catch (\Exception $e) {
            $this->_helper->logData($e->getMessage(),1);
            throw new \Magento\Framework\Exception\LocalizedException(__("Optimal Gateway Transaction Error: " . $e->getMessage()));
        }
    }

    /**
     * Authorize a payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Custom Log : ');

        if ($payment->getOrder()->getState() !=  \Magento\Sales\Model\Order::STATE_PROCESSING) {
            $payment->setIsTransactionPending(true);
        }

        if (!$this->canAuthorize()) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Authorize action is not available."));
        }

        try {
            $error = false;
            $customerSession = $this->_customerSession;
            $adminQuoteSession = $this->_sessionQuote;
            if ($customerSession->isLoggedIn()){
                $customerId = $customerSession->getId();
                $customer = $this->_customer->create()->load($customerId);
            } elseif($adminQuoteSession->getCustomerId()){
                $customer = $this->_customer->create()->load($adminQuoteSession->getCustomerId());
            }

            if ( $amount < 0 ) {
                $error = __('Invalid amount for capture.');
            }

            if ( $error !== false ) {
                throw new \Magento\Framework\Exception\LocalizedException(__("There was a problem authorizing the order."));
            }

            $order      = $payment->getOrder();
            $quote      = $this->_quoteFactory->create()->load($order->getQuoteId());
            $client     = $this->_hostedClient;
            $helper     = $this->_helper;

            $createProfile = false;

            $orderData      = array();
            $customerData   = array();
            // Get order data
            $orderData['remote_ip']         = $order->getRemoteIp();
            $orderData['order_items']       = $order->getAllVisibleItems();
            $orderData['increment_id']      = $order->getIncrementId();
            $orderData['customer_email']    = $order->getCustomerEmail();
            $orderData['billing_address']   = $order->getBillingAddress();
            $orderData['shipping_address']  = $order->getShippingAddress();

            // 20160419 - Tax correction for VAT hidden tax ticket #17
            $orderData['base_tax_amount']               = $order->getBaseTaxAmount() + $order->getBaseHiddenTaxAmount();

            $orderData['gift_cards_amount']             = $quote->getBaseGiftCardsAmountUsed();
            $orderData['base_grand_total']              = $order->getBaseGrandTotal();
            $orderData['base_currency_code']            = $order->getBaseCurrencyCode();
            $orderData['base_shipping_amount']          = $order->getBaseShippingAmount();
            $orderData['base_discount_amount']          = $order->getBaseDiscountAmount();
            $orderData['base_customer_balance_amount']  = $order->getBaseCustomerBalanceAmount();
            $orderData['base_amstcred_amount']          = $order->getBaseAwStoreCreditAmount();
            $orderData['base_tier_discount']            = $order->getBaseTierDiscount();
            $orderData['base_payment_charge']           = $order->getBasePaymentCharge();

            // 20150806 - CollinsHarper update for issue with 3rd party fees / rewards points.
            // if the totals do not match. we add the different to the discount.

            $pointsData = $this->_checkoutSession->getData('reward_sales_rules');
            if($pointsData && isset($pointsData['base_discount'])) {
                $orderData['gift_cards_amount'] += $pointsData['base_discount'];
            }
             // ---------------- added for amasty store credit -------------------
            if($orderData['base_amstcred_amount'] && isset($orderData['base_amstcred_amount'])) {
                $orderData['gift_cards_amount'] -= $orderData['base_amstcred_amount'];
            }
            // ---------------------- end here ----------------------------
            // ---------------- added for payment fee and discount store credit -------------------
            if($orderData['base_tier_discount'] && isset($orderData['base_tier_discount'])) {
                $orderData['gift_cards_amount'] -= $orderData['base_tier_discount'];
            }
            if($orderData['base_payment_charge'] && isset($orderData['base_payment_charge'])) {
                $orderData['gift_cards_amount'] -= $orderData['base_payment_charge'];
            }
            // ---------------------- end here ----------------------------

            $paymentData = $payment->getData();

            $skip3d         = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/skip3D', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $allowInterac   = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/allow_interac', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if (isset($paymentData['optimal_create_profile'])) {
                $createProfile = $paymentData['optimal_create_profile'];
            }

            $useInterac = false;
            if ($this->_useInterac($quote)) {
                $orderData['use_interac'] = 1;
                $createProfile = false;
                $useInterac = true;
            }

            $checkoutMethod = $this->_onepage->getCheckoutMethod();
            $quote = $this->_quoteFactory->create()->load($order->getQuoteId());
            $billing = $quote->getBillingAddress();
            $shipping = $order->getShippingAddress();
            $last = (string)$billing->getLastname() ? $billing->getLastname() : $shipping->getLastname();
            $first = (string)$billing->getFirstname() ? $billing->getFirstname() : $shipping->getFirstname();
            if ($checkoutMethod != 'guest') {
                
                $customerData['is_guest'] = false;
                $customerData['lastname'] = $last;
                $customerData['firstname'] = $first;
                $customerData['email'] = (string)$billing->getEmail();

            } elseif ($customerSession->isLoggedIn()){ // Get customer information
                $customerData = $customer->getData();
                $customerData['is_guest'] = false;
                $customerData['lastname'] = (string)$customer->getLastname();
                $customerData['firstname'] = (string)$customer->getFirstname();
                $customerData['email'] = (string)$customer->getEmail();

            } else {
                if ($createProfile) {
                    $customerData['is_guest'] = false;
                    $customerData['lastname'] = (string)$order->getCustomerLastname();
                    $customerData['firstname'] = (string)$order->getCustomerFirstname();
                    $customerData['email'] = (string)$order->getCustomerEmail();
                } else {
                    $customerData['is_guest'] = true;
                    $customerData['lastname'] = $last;
                    $customerData['firstname'] = $first;
                    $customerData['email'] = (string)$order->getCustomerEmail();
                }
            }
            
            $savedCreditCardProfileId = 0;
            if(isset($paymentData['optimal_profile_id']) && $paymentData['optimal_profile_id'] > 0) {
                $savedCreditCardProfileId = $customerData['profile_id'] = $paymentData['optimal_profile_id'];
            }
            // Call the helper and get the data for netbank
            $data = $helper->prepareNetbanksOrderData($orderData ,$customerData, $createProfile);
            // Call Netbanks API and create the order
            $response = $client->createOrder($data);
            $logger->info(print_r($response,1));
            //print_r($response); EXIT;
            if (isset($response['link'])) {
                foreach ($response['link'] as $link) {
                    if($link['rel'] === 'hosted_payment') {
                        $postURL = $link['uri'];
                    }
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("There was a problem creating the order"));
            }
            
            // Redirect the Customer if 3D-Secure verification is turned on
            if (isset($postURL) && (!$skip3d || ($allowInterac && $useInterac))) {

                try {

                    $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                    $order->setStatus('pending_payment');
                    $order->addStatusHistoryComment('Redirecting the Customer to Optimal Payments for Payment Authorisation', 'pending_payment');
                    $logger->info('Response ID : ');

                    $responseId='';
                    if(isset($response['id'])){
                        $responseId=$response['id'];
                    }
                    //MerchantResfNum:
                    $merchantRefNum='';
                    if(isset($response['merchantRefNum'])){
                        $merchantRefNum=$response['merchantRefNum'];
                    }
                     $logger->info('Response ID &&  merchantRefNum: ');
                     $logger->info($responseId.' -- '.$merchantRefNum);

                    
                    $order->addStatusHistoryComment(
                        'Netbanks Order Id: ' . $responseId .'<br/>' .
                        'Reference: # ' . $merchantRefNum .'<br/>'
                    );
                    $order->setIsNotified(false);
                    $order->save();
                    $checkoutSess = $this->_checkoutSession;
                    $checkoutSess->unsQuoteId();
                    $checkoutSess->unsRedirectUrl();

                    $payment->setStatus('PENDING');
                    $payment->setAdditionalInformation('order', serialize(array('id' => $responseId)));
                    $payment->setTransactionId($responseId);
                    // magento will automatically close the transaction on auth preventing the invoice from being captured online.
                    $payment->setIsTransactionClosed(false);
                    $payment->save();

                    $this->orderRedirectUrl($postURL);
                    //$this->responseFactory->create()->setRedirect($postURL)->sendResponse();

                } catch (\Exception $e) {
                    $this->_helper->logData($e->getMessage(),1);
                    $this->_messageManager->addError(__('An error was encountered while redirecting to the payment gateway, please try again later.'));
                    $this->_handlePaymentFailure();
                    //$this->responseFactory->create()->setRedirect($this->_storeManager->getStore()->getBaseUrl() . 'checkout/cart')->sendResponse();
                    $this->orderRedirectUrl($this->_storeManager->getStore()->getBaseUrl() . 'checkout/cart');
                }

                return $this;
            }

            if(isset($postURL)) {
                $paymentData = $this->_preparePayment($payment->getData());

                if(isset($customerData['profile_id']))
                {
                    unset($paymentData['cardNum']);
                    unset($paymentData['cardExpiryMonth']);
                    unset($paymentData['cardExpiryYear']);
                    $paymentData['id'] = $customerData['profile_id'];
                    $paymentData['paymentToken'] = $data['profile']['paymentToken'];
                }
               // echo 'res'.$response['id']; exit;
                $paymentResponse    = $client->submitPayment($postURL,$paymentData);
                $logger->info('Final response : ');
                 $logger->info(print_r($paymentResponse,1));
                $orderStatus        = $this->_getOptimalOrderStatus($client, $response['id']);
                
                if (!isset($orderStatus['transaction']))
                {
                    $this->_helper->logData('Aborting ... Transaction Object not present in orderStatus');
                    throw new \Magento\Framework\Exception\LocalizedException(__("Something went wrong with your transaction. Please contact support."));
                }

                $transaction        = $orderStatus['transaction'];

                // Now we need to check the payment status if the transaction is available
                if($transaction['status'] == 'declined' || $transaction['status'] == 'cancelled' || $transaction['status'] == 'errored')
                {
                    throw new \Magento\Framework\Exception\LocalizedException(__("There was an error processing your payment"));
                }

                // Check the order status for the profile information and try to save it
                if($createProfile){
                    if(isset($orderStatus['profile'])){
                        $profile = $this->_creditcard->create();
                        if (isset($orderStatus['profile']['merchantCustomerId'])) {
                            $merchantCustomerId = $orderStatus['profile']['merchantCustomerId'];
                        }

                        if(!isset($merchantCustomerId))
                        {
                            $merchantCustomerId = $this->_helper->getMerchantCustomerId($order->getCustomerId());
                            $merchantCustomerId = $merchantCustomerId['merchant_customer_id'];
                        }

                        // Set Profile Info
                        $profile->setCustomerId($order->getCustomerId());
                        $profile->setProfileId($orderStatus['profile']['id']);
                        $profile->setMerchantCustomerId($merchantCustomerId);
                        $profile->setPaymentToken($orderStatus['profile']['paymentToken']);

                        // Set Nickname
                        $cardName = $orderStatus['transaction']['card']['brand'];
                        $profile->setCardNickname($this->_helper->processCardNickname($cardName));

                        // Set Nickname
                        //$cardHolder = $payment->getCcOwner();
                        $cardHolder = $customerData['firstname'] . ' ' . $customerData['lastname']; // $params['firstname'] . $params['lastname'];
                        $profile->setCardHolder($cardHolder);

                        // Set Card Info
                        $lastfour = $payment->getCcLast4();
                        $profile->setLastFourDigits($lastfour);

                        // Format card expiration date [todo]: Make a helper function
                        $expirationDate = sprintf("%02s", $paymentData['cardExpiryMonth']) . "/"  . substr($paymentData['cardExpiryYear'], -2);

                        $profile->setCardExpiration($expirationDate);

                        $profile->save();
                    }else {
                        throw new \Magento\Framework\Exception\LocalizedException(__("There was a problem saving your payment information."));
                    }
                }



                $order->addStatusHistoryComment(
                    'Netbanks Order Id: ' . $orderStatus['id'] .'<br/>' .
                    'Reference: # ' . $orderStatus['merchantRefNum'] .'<br/>' .
                    'Transaction Id: ' . $transaction['confirmationNumber'] .'<br/>' .
                    'Status: ' . $transaction['status'] .'<br/>'
                );

                $payment->setStatus('APPROVED');
                $payment->setAdditionalInformation('order', $this->_serializer->serialize(array('id' => $orderStatus['id'], 'optimal_profile_id' => $savedCreditCardProfileId)));
                $payment->setAdditionalInformation('transaction', $this->_serializer->serialize($transaction));
                $payment->setTransactionId($orderStatus['id']);
                // magento will automatically close the transaction on auth preventing the invoice from being captured online.
                // 20161027 - sharper - issue with onsite authorize
                $payment->setIsTransactionPending(false);
                $payment->setIsTransactionClosed(false);
                $payment->setAdditionalInformation('payment_type', $this->getInfoInstance()->getCcType());


            }

            return $this;
        } catch (\Exception $e) {
            $this->_helper->logData($e->getMessage(),1);
            throw new \Magento\Framework\Exception\LocalizedException(__("Optimal Gateway Transaction Error: " . $e->getMessage()));
            $this->_helper->cleanMerchantCustomerId($this->_customerSession->getId());
        }
        
    }

    /**
     * 
     * @param type $paymentData
     * @return type
     */
    protected function _preparePayment($paymentData)
    {
        
        $fPaymentData = array(
            'cardNum'               => (string) $paymentData['cc_number'],
            'cardExpiryMonth'       => (int) $paymentData['cc_exp_month'],
            'cardExpiryYear'        => (int) $paymentData['cc_exp_year'],
            'cvdNumber'             => (string) $paymentData['cc_cid'],
        );

        if(isset($paymentData['optimal_create_profile']))
        {
            $fPaymentData['storeCardIndicator'] = (bool) $paymentData['optimal_create_profile'];
        }

        return $fPaymentData;
    }

    /**
     * 
     * @param type $client
     * @param type $id
     * @param type $count
     * @return type
     */
    public function getOptimalOrderStatus($client, $id, $count = 0) {
        return $this->_getOptimalOrderStatus($client, $id, $count);
    }

    /**
     * 
     * @param type $client
     * @param type $id
     * @param type $counter
     * @return type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getOptimalOrderStatus($client, $id, $counter = 0)
    {
        if($counter >= 3){
            throw new \Magento\Framework\Exception\LocalizedException(__("There was a problem retrieving the order information. Please contact support."));
        }

        $this->_helper->logData('Get-Optimal-Order-Status Try #: ' . ($counter + 1) );

        try{
            return $client->retrieveOrder($id);
        } catch (\ClassyLlama\LlamaCoin\Model\CustomException $e) { // in case when Error is generated from Optimal
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        } catch(\Exception $e) {
            $counter++;
            $this->_getOptimalOrderStatus($client, $id, $counter);
        }

    }

    /**
     * 
     * @param type $quote
     * @return type
     */
    protected function _useInterac($quote) {
        $quotePayment = $quote->getPayment();
        $useInterac = $quotePayment->getOptimalUseInterac();

        $allowInterac = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/allow_interac', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $allowInterac && $useInterac;
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        // TODO we always pretend we are authorize.. on return we do capture
        if(!$this->_scopeConfig->getValue('payment/classyllama_llamacoin/skip3D', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
        }
         return $this->getConfigData('payment_action');
    }
    
    /**
     * 
     * @param \Magento\Framework\DataObject $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        try {
            $additionalInformation = $payment->getAdditionalInformation();

            if (isset($additionalInformation['transaction'])) {
                $order = $payment->getOrder();

                $client = $this->_hostedClient->create(); //Mage::getModel('optimal/hosted_client', array('store_id' => $order->getStoreId()));

                $paymentData    = $this->_serializer->unserialize($additionalInformation['transaction']);
                $orderData      = $this->_serializer->unserialize($additionalInformation['order']);

                $transactionStatus = $client->retrieveOrder($orderData['id']);

                if ($transactionStatus->transaction->status == 'held')
                {
                    // Prepare api order update
                    $data = array(
                        'transaction' => array(
                            'status' => 'cancelled'
                        )
                    );

                    $response = $client->updateOrder($data, $orderData['id']);

                } elseif($transactionStatus->transaction->status == 'success') {
                    $response = $client->cancelOrder($orderData['id']);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to void transaction.'));
                }

                $payment
                    ->setIsTransactionClosed(1)
                    ->setShouldCloseParentTransaction(1);


                $order->addStatusHistoryComment(
                    'Transaction Voided <br/>' .
                    'Trans Type: ' . $response->authType .'<br/>'.
                    'Confirmation Number: ' . $response->confirmationNumber .'<br/>'.
                    'Transaction Amount: ' . $response->amount/100 .'<br/>'
                );

                return $this;


            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Transaction information is not properly set."));
            }
        } catch (\Exception $e) {
            $this->_helper->logData($e->getMessage(),1);
            throw new \Magento\Framework\Exception\LocalizedException(__("Optimal Gateway Transaction Error: " . $e->getMessage()));
        }
    }

    /**
     * 
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param type $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $helper = $this->_helper;

        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Invalid amount for refund."));
        }

        if (!$payment->getParentTransactionId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Invalid transaction ID."));
        }

        try {
            $additionalInformation = $payment->getAdditionalInformation();

            if (isset($additionalInformation['transaction'])) {
                $order = $payment->getOrder();

                $client = $this->_hostedClient->create(); //Mage::getModel('optimal/hosted_client', array('store_id' => $order->getStoreId()));

                $paymentData    = $this->_serializer->unserialize($additionalInformation['transaction']);
                $orderData      = $this->_serializer->unserialize($additionalInformation['order']);

                $data = array(
                    'amount'            => (int)$helper->formatAmount($amount),
                    'merchantRefNum'    => (string)$paymentData->merchantRefNum
                );

                if(is_null($paymentData->associatedTransactions[0]->reference))
                {
                    $transactionId = $payment->getLastTransId();
                }else {
                    $transactionId = $paymentData->associatedTransactions[0]->reference;

                }

                $response = $client->refundOrder($data,$transactionId);
                $order->addStatusHistoryComment(
                    'Trans Type: ' . $response->authType .'<br/>',
                    'Confirmation Number: ' . $response->confirmationNumber .'<br/>',
                    'Transaction Amount: ' . $response->amount/100 .'<br/>'
                );
                return $this;

            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Transaction information is not properly set."));
            }
        } catch (\Exception $e) {
            $this->_helper->logData($e->getMessage(),1);
            throw new \Magento\Framework\Exception\LocalizedException(__("Optimal Gateway Transaction Error: " . $e->getMessage()));
        }

        return $this;
    }

    /**
     * 
     * @staticvar type $gatewayUrl
     * @param type $url
     * @return type
     */
    public function orderRedirectUrl($url = null)
    {
        static $gatewayUrl;

        if (!$url) {
            return $gatewayUrl;
        }

        $gatewayUrl = $url;

        return $gatewayUrl;
    }

    /**
     * 
     * @return type
     */
    public function getOrderPlaceRedirectUrl()
    {

        return $this->orderRedirectUrl();
    }

    /**
     * Handle Payment Failure
     * - Cancel the order
     * - Restore the quote
     *
     * Cancel Order and attempt to restore cart.
     *
     */
    protected function _handlePaymentFailure()
    {
        $session = $this->_checkoutSession;

        if ($session->getLastRealOrderId()) {
            try {
                $order = $this->_order->loadByIncrementId($session->getLastRealOrderId());
                if ($order->getId()) {
                    $order->cancel()->save();
                    $quote = $this->_quoteFactory->create()->load($order->getQuoteId());
                    if ($quote->getId()) {
                        $quote->setIsActive(1)
                            ->setReservedOrderId(null)
                            ->save();

                        $session->replaceQuote($quote)
                            ->unsLastRealOrderId();
                    }
                }
            } catch (\Exception $e) {
                $this->_helper->logData($e->getMessage(),1);
            }
        }
    }
    /**
     * Set the payment action to authorize_and_capture
     *
     * @return string
     *
    public function getConfigPaymentAction()
    {
        return self::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * Test method to handle an API call for authorization request.
     *
     * @param $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     *
    public function makeAuthRequest($request)
    {
        $response = ['transactionId' => 123]; //todo implement API call for auth request.

        if(!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Failed auth request.'));
        }

        return $response;
    }

    /**
     * Test method to handle an API call for capture request.
     *
     * @param $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     *
    public function makeCaptureRequest($request)
    {
        $response = ['success']; //todo implement API call for capture request.

        if(!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Failed capture request.'));
        }

        return $response;
    } */
}