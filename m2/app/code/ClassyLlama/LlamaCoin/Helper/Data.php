<?php

namespace ClassyLlama\LlamaCoin\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    
    const LOG_FILE_NAME = 'optimal_error.log';
    
    protected $_merchantcustomer;
    protected $_errorcode;
    protected $_storeManager;
    protected $_encryptor;
    protected $_scopeConfig;
    protected $_request;
    protected $_creditcard;
    protected $_customerSession;
    protected $_coreSession;
    protected $_checkoutSession;
    protected $_quoteFactory;
    
    /**
     * 
     * @param \ClassyLlama\LlamaCoin\Model\MerchantCustomerFactory $merchantcustomer
     * @param \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard
     * @param \ClassyLlama\LlamaCoin\Model\ErrorcodeFactory $errorcode
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \ClassyLlama\LlamaCoin\Model\MerchantCustomerFactory $merchantcustomer,
        \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard,
        \ClassyLlama\LlamaCoin\Model\ErrorcodeFactory $errorcode,    
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,   
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,    
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\App\RequestInterface $request,   
        \Magento\Framework\Session\SessionManagerInterface $coreSession,    
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig    
        ) {
            $this->_merchantcustomer = $merchantcustomer;
            $this->_errorcode = $errorcode;
            $this->_encryptor = $encryptor;
            $this->_scopeConfig = $scopeConfig;
            $this->_request = $request;
            $this->_creditcard = $creditcard;
            $this->_customerSession = $customerSession;
            $this->_checkoutSession = $checkoutSession;
            $this->_coreSession = $coreSession;
            $this->_quoteFactory = $quoteFactory;
            $this->_storeManager = $storeManagerInterface;
    }
    /**
     * @param $amount
     * @return int
     */
    public function formatAmount($amount) {
        $amount = (int) (string) ((number_format($amount, 2, '.', '')) * 100);
        return $amount;
    }

    /**
     * 
     * @param type $message
     * @param type $f
     */
    public function logData($message,$f=0){
        $file = '/var/log/'.self::LOG_FILE_NAME;
        if($f){
            $file = '/var/log/optipayment.log';
        }
        $writer = new \Zend\Log\Writer\Stream(BP .$file );
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
    }
    
    /**
     * @param $address
     * @param $position
     * @return string
     */
    public function getStreetDetails($address, $position) {
        if (isset($address[$position])) {
            return $address[$position];
        } else {
            return '';
        }
    }

    /**
     * 
     * @param type $customer_id
     * @return type
     */
    public function getMerchantCustomerId($customer_id = false) {
      
        if ($customer_id !== false) {
            $customer = $this->_merchantcustomer->create();
            $customer->load($customer_id,'customer_id');
            if ($customer->getMerchantCustomerId()) {
                return $this->processMerchantCustomerId($customer);
            }
        }
        $customer = $this->_merchantcustomer->create();
        if ($customer_id) {
            $customer->setCustomerId($customer_id);
        }
        $customer->setDataChanges(true); // force save in case we need to save just the ID.
        $customer->save();

        return $this->processMerchantCustomerId($customer);
    }

    /**
     * 
     * @param type $merchantCustomer
     * @return type
     */
    protected function processMerchantCustomerId($merchantCustomer) {
        $internalId = $merchantCustomer->getMerchantCustomerId();
        $customerId = $merchantCustomer->getCustomerId();
        $currentStoreId = $this->_storeManager->getStore()->getId();
        $apiLoginId = $this->_encryptor->decrypt($this->_scopeConfig->getValue('payment/classyllama_llamacoin/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        // KL: Here is the potential bug, if customer have more then 1 sites and they try to use the same
        //     API Key for test, they are going to experience the issue on duplicate profile merchant customer id
        //     The following patch is designed to address this issue
        if (strlen(trim($merchantCustomer->getData('generated_merchant_id'))) > 0) {
            $generatedId = $merchantCustomer->getData('generated_merchant_id');
        } else {
            $generatedId = md5($apiLoginId . $internalId . $customerId . $this->_request->getHttpHost());
            //md5($apiLoginId . $internalId . $customerId . $this->gen_uuid() . time());
        }

        if (!$customerId) {

            $merchantCustomer->setData('generated_merchant_id', $generatedId);
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(__METHOD__ . __LINE__ . print_r($merchantCustomer->getData(), 1));
            
            $merchantCustomer->setDataChanges(true);
            $merchantCustomer->save();
            $this->_coreSession->setOptimalAnonymousGeneratedCustomerId($generatedId);
        }
        return array(
            'internal_id' => $internalId,
            // KL: We would like to make this value refresh everytime it get called
            'merchant_customer_id' => $generatedId
        );
    }

    /**
     * 
     * @param type $customer_id
     */
    public function cleanMerchantCustomerId($customer_id = false) {
        if ($customer_id) {
            $cards = $this->_creditcard->create()->getCollection();
            $cards->addFieldToFilter('customer_id', array('eq', $customer_id));
            if ($cards->count() == 0) {
                $this->_creditcard->create()->load($customer_id, 'customer_id')->delete();
            }
        }
    } 

    /**
     * 
     * @param type $nickname
     * @return type
     */
    function processCardNickname($nickname) {
        return str_replace('Amex', 'American Express', str_replace('Mastercard', 'MasterCard', ucwords($nickname)));
    }

    /**
     * Prepare data for netbanks order creation
     *
     * @param $orderData
     * @param $customerData
     * @param bool $saveCard
     * @param null $transactionMode
     * @return array
     */
    public function prepareNetbanksOrderData($orderData, $customerData, $saveCard = false, $transactionMode = null) {

        $shoppingCartArray = array();
        $orderItems = $orderData['order_items'];
        $billingAddress = $orderData['billing_address'];
        $shippingAddress = $orderData['shipping_address'];
        $storeId = $this->_storeManager->getStore()->getId();

        // Order extended options
        $extendedOptionsArray = array();

        // Minimum order information needed
        $data = array(
            'totalAmount' => (int) $this->formatAmount($orderData['base_grand_total']),
            'currencyCode' => (string) $orderData['base_currency_code'],
            'merchantRefNum' => (string) $orderData['increment_id'] . time()
        );

        $useInterac = false;
        if (isset($orderData['use_interac'])) {
            $useInterac = $orderData['use_interac'];
            unset($orderData['use_interac']);
        }

        if ($useInterac) {
            $data['paymentMethod'] = array('interac');
        }

        if (strlen($this->_scopeConfig->getValue('payment/classyllama_llamacoin/merchant_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) > 0) {
            $data['merchantNotificationEmail'] = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/merchant_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        $data['customerNotificationEmail'] = (string) $orderData['customer_email'];
        if ($this->_scopeConfig->getValue('payment/classyllama_llamacoin/email_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != 1) {
            $extendedOptionsArray[] = array(
                'key' => (string) 'suppressCustomerEmail',
                'value' => true
            );
        }

        // Customer Profile information
        // 1) The customer wants to pay with a saved profile
        //    - We check for a profile id and a payment token
        // 2) The customer wants to add a new card to his profile
        //    - We check for the customer profile id

        $customerProfile['lastName'] = (string) $customerData['lastname'];
        $customerProfile['firstName'] = (string) $customerData['firstname'];
        $merchantCustomerId = null;

        $skip3d = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/skip3D', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $profileEditingEnabled = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/profile_edit_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $profilesEnabled = $this->_scopeConfig->getValue('payment/classyllama_profiles/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$customerData['is_guest']) {

            if (!isset($customerId)) {
                $customerId = $this->_customerSession->getId();
            }

            $merchantCustomerId = $this->getMerchantCustomerId($customerId);
            $merchantCustomerId = $merchantCustomerId['merchant_customer_id'];
            $profile = $this->_creditcard->create()->loadByMerchantCustomerId($merchantCustomerId);


            // If not skipping 3D and CreateProfiles is TRUE
            if (!$skip3d && $profilesEnabled) {
                // KL: Logic bug here, we shall not create the profile record until we have success completed transaction
                if ($profile->getProfileId()) {

                    $customerProfile['id'] = (string) $profile->getProfileId();

                    /*            if (empty($customerProfile['merchantCustomerId'])) {
                      $merchantCustomerId = $this->getMerchantCustomerId($customerId);
                      $merchantCustomerId = $merchantCustomerId['merchant_customer_id'];
                      $customerProfile['merchantCustomerId']  = $merchantCustomerId;
                      } */
                    unset($customerProfile['merchantCustomerId']);
                } else {
                    if (empty($customerProfile['merchantCustomerId'])) {
                        $merchantCustomerId = $this->getMerchantCustomerId($customerId);
                        $merchantCustomerId = $merchantCustomerId['merchant_customer_id'];
                        $customerProfile['merchantCustomerId'] = $merchantCustomerId;
                    }
                    $customerProfile['merchantCustomerId'] = $customerProfile['merchantCustomerId'] . '-' . time();
                }
            } elseif (!$skip3d && !$profilesEnabled) {

                if ($profile->getProfileId()) {
                    $customerProfile['id'] = $profile->getProfileId();
                }
            } else {
                if (!empty($customerData['profile_id'])) { // Check if there is a profile_id being passed
                    $profile = $this->_creditcard->create()->load((int) $customerData['profile_id']);
                    if ($profile->getProfileId()) {
                        $customerProfile['id'] = (string) $profile->getProfileId();

                        // In case the profile exists
                        if (!$saveCard) {
                            $customerProfile['paymentToken'] = (string) $profile->getPaymentToken();
                        }
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(__("The select profile does not exists."));
                    }
                } elseif ($saveCard) {

                    // Check for existing profile id
                    $customerId = $this->_customerSession->getId();
                    $merchantCustomerId = $this->getMerchantCustomerId($customerId);
                    $merchantCustomerId = $merchantCustomerId['merchant_customer_id'];
                    $profile = $this->_creditcard->create()->loadByMerchantCustomerId($merchantCustomerId);

                    if ($profile->getProfileId()) {
                        $customerProfile['id'] = (string) $profile->getProfileId();
                    } else {
                        $customerProfile['merchantCustomerId'] = $merchantCustomerId;
                    }
                }
            }
        }

        // Need to be sure this matches the store on which the order was placed
        if (is_null($transactionMode)) {
            $transactionMode = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/payment_action', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        switch ($transactionMode) {
            case \ClassyLlama\LlamaCoin\Model\Config\Transaction::AUTH_VALUE:
                $extendedOptionsArray[] = array(
                    'key' => (string) 'authType',
                    'value' => (string) \ClassyLlama\LlamaCoin\Model\Config\Transaction::AUTH
                );
                break;
            case \ClassyLlama\LlamaCoin\Model\Config\Transaction::CAPT_VALUE:
                $extendedOptionsArray[] = array(
                    'key' => (string) 'authType',
                    'value' => (string) \ClassyLlama\LlamaCoin\Model\Config\Transaction::CAPT
                );
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(__("There is no transaction method set, please contact the website administrator."));
                break;
        }

        if ($skip3d) {
            $extendedOptionsArray[] = array(
                'key' => (string) 'skip3D',
                'value' => true
            );
            // since we are skipping 3D-Secure check, hence we can process the order via Silent Post
            $extendedOptionsArray[] = array(
                'key' => 'silentPost',
                'value' => true
            );
        } else {
            $extendedOptionsArray[] = array(
                'key' => (string) 'skip3D',
                'value' => false
            );

            if ($profileEditingEnabled) {
                $extendedOptionsArray[] = array(
                    'key' => 'disablePaymentPageEditing',
                    'value' => false
                );
            } else {
                $extendedOptionsArray[] = array(
                    'key' => 'disablePaymentPageEditing',
                    'value' => true
                );
            }
        }

        $addendumDataArray = array();
        $threatMetrixId = $this->_coreSession->getThreatMetrixSessionKey();

        if (isset($threatMetrixId) && $this->_scopeConfig->getValue('payment/classyllama_threat_metrix/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $extendedOptionsArray[] = array(
                'key' => (string) 'threatMetrixSessionId',
                'value' => $threatMetrixId
            );
        }

        // Ancillary fees information
        $ancillaryFeesArray = array(
            array(
                'amount' => (int) $this->formatAmount($orderData['base_shipping_amount']),
                'description' => "Shipping Amount"
            ),
            array(
                'amount' => (int) $this->formatAmount($orderData['base_tax_amount']),
                'description' => "Tax Amount"
            ),
            array(
                'amount' => (int) $this->formatAmount($orderData['base_discount_amount']),
                'description' => "Discount Amount"
            ),
            array(
                'amount' => (int) $this->formatAmount($orderData['base_customer_balance_amount'] * -1),
                'description' => "Store Credit Amount"
            )
        );

        if (!empty($orderData['gift_cards_amount'])) {
            $ancillaryFeesArray[] = array(
                'amount' => (int) $this->formatAmount($orderData['gift_cards_amount'] * -1),
                'description' => "Gift Cards Amount"
            );
        }

        // Billing Details information
        $billingDetailsArray = array(
            'city' => (string) $billingAddress->getCity(),
            'country' => (string) $billingAddress->getCountryId(),
            'street' => (string) $this->getStreetDetails($billingAddress->getStreet(), 0),
            'street2' => (string) $this->getStreetDetails($billingAddress->getStreet(), 1),
            'zip' => (string) $billingAddress->getPostcode(),
            'phone' => (string) $billingAddress->getTelephone(),
        );

        if ($billingDetailsArray['street2'] == '') {
            unset($billingDetailsArray['street2']);
        }

        if ($billingAddress->getCountryId() === 'CA' || $billingAddress->getCountryId() === 'US') {
            $billingDetailsArray['state'] = (string) $billingAddress->getRegionCode();
        } else {
            if ((string) $billingAddress->getRegion() != '') {
                $billingDetailsArray['state'] = (string) $billingAddress->getRegion();
            }
        }

        // Start Refactor : Work around for downloadable and virtual products
        if (!$shippingAddress) {
            $shippingAddress = $billingAddress;
        }
        // End Refactor

        if (!$customerData['is_guest'] && $billingAddress->getCustomerAddressId() === $shippingAddress->getCustomerAddressId()) {
            $billingDetailsArray['useAsShippingAddress'] = true;
        } else {
            $billingDetailsArray['useAsShippingAddress'] = false;
        }

        // Shipping Details information
        $shippingDetailsArray = array(
            'city' => (string) $shippingAddress->getCity(),
            'country' => (string) $shippingAddress->getCountryId(),
            'street' => (string) $this->getStreetDetails($shippingAddress->getStreet(), 0),
            'street2' => (string) $this->getStreetDetails($shippingAddress->getStreet(), 1),
            'zip' => (string) $shippingAddress->getPostcode(),
            'phone' => (string) $shippingAddress->getTelephone(),
        );


        if ($shippingDetailsArray['street2'] == '') {
            unset($shippingDetailsArray['street2']);
        }

        if ($shippingAddress->getCountryId() === 'CA' || $shippingAddress->getCountryId() === 'US') {
            $shippingDetailsArray['state'] = (string) $shippingAddress->getRegionCode();
        } else {
            if ((string) $shippingAddress->getRegion() != '') {
                $shippingDetailsArray['state'] = (string) $shippingAddress->getRegion();
            }
        }

        // Shopping Cart Information
        foreach ($orderItems as $item) {
            $itemArray = array(
                'amount' => (int) $this->formatAmount($item->getBasePrice()),
                'quantity' => (int) $item->getQtyOrdered(),
                'sku' => (string) $item->getSku(),
                'description' => (string) substr($item->getName(), 0, 45)
            );

            $shoppingCartArray[] = $itemArray;
        }

        // Callback information
        $callbackArray = array();
        $callbackArray[] = array(
            'format' => (string) 'json',
            'rel' => (string) 'on_success',
            'retries' => (int) \ClassyLlama\LlamaCoin\Model\Hosted\Client::CONNECTION_RETRIES,
            'returnKeys' => array(
                'id',
                'transaction.confirmationNumber',
                'transaction.status'
            ),
            'synchronous' => true,
            'uri' => $this->_storeManager->getStore()->getBaseUrl() . 'classy/handler/callback'
        );

        // Callback information
        $redirectArray = array();
        $returnKeys = array('id', 'transaction.confirmationNumber', 'transaction.status', 'profile.id', 'profile.paymentToken');

        $redirectArray[] = array(
            'rel' => (string) 'on_success',
            'returnKeys' => $returnKeys,
            'uri' => $this->_storeManager->getStore()->getBaseUrl() . 'classy/handler/callback'
        );
        $redirectArray[] = array(
            'rel' => (string) 'on_error',
            'returnKeys' => $returnKeys,
            'uri' => $this->_storeManager->getStore()->getBaseUrl() . 'classy/handler/callback'
        );
        $redirectArray[] = array(
            'rel' => (string) 'on_decline',
            'returnKeys' => $returnKeys,
            'uri' => $this->_storeManager->getStore()->getBaseUrl() . 'classy/handler/callback'
        );
        $redirectArray[] = array(
            'rel' => (string) 'on_timeout',
            'returnKeys' => $returnKeys,
            'uri' => $this->_storeManager->getStore()->getBaseUrl() . 'classy/handler/callback'
        );
        $redirectArray[] = array(
            'rel' => (string) 'on_hold',
            'returnKeys' => $returnKeys,
            'uri' => $this->_storeManager->getStore()->getBaseUrl() . 'classy/handler/callback'
        );

        // Add extra information to the order Data
        $data['shoppingCart'] = $shoppingCartArray;
        $data['ancillaryFees'] = $ancillaryFeesArray;
        $data['billingDetails'] = $billingDetailsArray;
        $data['shippingDetails'] = $shippingDetailsArray;
        $data['redirect'] = $redirectArray;
        $data['profile'] = $customerProfile;
        $data['extendedOptions'] = $extendedOptionsArray;
        $data['addendumData'] = $addendumDataArray;
        return $data;
    }

    /**
     * 
     * @param type $code
     * @return type
     */
    public function getMsgByCode($code = null) {
        $model = $this->_errorcode->create()->loadByCode($code);
        if (!$model->getCode()) {
            return null;
        }

        $msg = $model->getMessage();
        $customMsg = $model->getCustomMessage();

        if ($model->getActive() == 1) {
            return $customMsg ? $customMsg : $msg;
        }

        return $msg;
    }

    /**
     * 
     * @return boolean
     */
    public function restoreQuote() {
        $checkoutSession = $this->_checkoutSession;
        $order = $checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            $quote = $this->_quoteFactory->create()->load($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(1)
                        ->setReservedOrderId(null)
                        ->save();
                $checkoutSession->replaceQuote($quote)
                        ->unsLastRealOrderId();
                return true;
            }
        }
        return false;
    }

    /**
     * 
     * @return type
     */
    public function canShow() {
        $skip3d = $this->_scopeConfig->getValue('payment/classyllama_llamacoin/skip3D', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $profilesEnabled = $this->_scopeConfig->getValue('payment/classyllama_profiles/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $show = ($profilesEnabled && $skip3d);

        return $show;
    }

    /**
     * gen UUID
     *
     * @return string
     */
    public function gen_uuid() {
        return sprintf(
                '%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

}
