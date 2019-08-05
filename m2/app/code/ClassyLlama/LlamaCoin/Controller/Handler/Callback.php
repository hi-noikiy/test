<?php

namespace ClassyLlama\LlamaCoin\Controller\Handler;

class Callback extends \Magento\Framework\App\Action\Action
{
    protected $_checkoutSession;
    protected $_customerSession;
    protected $_coreSession;
    protected $_messageManager;
    protected $_creditcard;
    protected $_helper;
    protected $_order;
    protected $_hosted;
    protected $_hostedClient;
    protected $_resource;
    protected $_customer;
    protected $_scopeConfig;
    
    /**
     * 
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard
     * @param \ClassyLlama\LlamaCoin\Helper\Data $helper
     * @param \ClassyLlama\LlamaCoin\Model\Hosted $hosted
     * @param \ClassyLlama\LlamaCoin\Model\Hosted\Client $hostedClient
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,    
        \Magento\Sales\Api\Data\OrderInterface $order,    
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,     
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \ClassyLlama\LlamaCoin\Model\CreditcardFactory $creditcard,
        \ClassyLlama\LlamaCoin\Helper\Data $helper,    
        \ClassyLlama\LlamaCoin\Model\Hosted $hosted,    
        \ClassyLlama\LlamaCoin\Model\Hosted\Client $hostedClient,    
        \Magento\Framework\App\ResourceConnection $resource,    
        \Magento\Customer\Model\CustomerFactory $customer,    
        \Magento\Framework\App\Action\Context $context    
    ){
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_coreSession = $coreSession;
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
        $this->_hostedClient = $hostedClient;
        $this->_hosted = $hosted;
        $this->_creditcard = $creditcard;
        $this->_order = $order;
        $this->_scopeConfig = $scopeConfig;
        $this->_resource = $resource;
        $this->_customer = $customer;
        
    }
    /**
     * 
     * @return type
     */
    public function execute()
    {
        $params  = $this->getRequest()->getParams();
        $session = $this->_checkoutSession;
        $status  = $params['transaction_status'];

        if ($status != 'success') {
            $this->_messageManager->addError(__('Payment failed, please review your payment information and try again.'));
            $this->_handlePaymentFailure($session, $params);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;
        }

        try {
            $this->_handlePaymentSuccess($session, $params);
        } catch (\Exception $e) {
            $this->logData($e);
            $this->_handlePaymentFailure($session, $params);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/failure');
            return $resultRedirect;
        }

    }
    
    /**
     * 
     * @param \Exception $e
     */
    public function logData(\Exception $e){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/optipayment.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($e->__toString());
    }

    /**
     * Handle Payment Failure
     * Cancel Order and attempt to restore cart.
     * @param type $session
     * @param type $params
     */
    protected function _handlePaymentFailure($session, $params)
    {
        $status             = $params['transaction_status'];
        $confirmation       = $params['transaction_confirmationNumber'];
        $optimalOrderId     = $params['id'];
        $profileId          = $params['profile_id'];
        $paymentToken       = $params['profile_paymentToken'];
        $profile            = $this->_creditcard->create()->loadByProfileId($profileId);

        $customerId         = $this->_customerSession->getId();
        $merchantCustomerId = $this->_helper->getMerchantCustomerId($customerId);
        $merchantCustomerId = $merchantCustomerId['merchant_customer_id'];

        // Check if profile exists
        if (!$profile->getId()) {
            // Make one otherwise
            $profile->setCustomerId($customerId);
            $profile->setProfileId($profileId);
            $profile->setPaymentToken($paymentToken);
            $profile->setMerchantCustomerId($merchantCustomerId);
            $profile->setIsDeleted(1);
            $profile->save();
        }

        if ($session->getLastRealOrderId()) {
            $order = $this->_order->loadByIncrementId($session->getLastRealOrderId());
            $payment = $order->getPayment();

            $order->addStatusHistoryComment(
                'Netbanks Order Id: ' . $optimalOrderId .'<br/>' .
                'Transaction Id: ' . $confirmation .'<br/>' .
                'Status: ' . $status .'<br/>'
            );

            $payment->setStatus('DECLINED');
            $payment->setAdditionalInformation('order', serialize(array('id' => $optimalOrderId)));

            $payment->setTransactionId($optimalOrderId);
            // magento will automatically close the transaction on auth preventing the invoice from being captured online.
            $payment->setIsTransactionClosed(true);
            $payment->setIsTransactionPending(false);

            $payment->save();

            try {
                if ($order->getId()) {
                    $order->cancel()->save();
                }
                $this->_helper->restoreQuote();
            } catch (\Exception $e) {
                $this->logData($e);
            }
        }

    }

    /**
     * Handle Payment Success
     * Update Order status and create invoice
     * @param $session
     * @param $params
     */
    protected function _handlePaymentSuccess($session, $params)
    {
        $optimalOrderId     = $params['id'];

        $order = $this->_order->loadByIncrementId($session->getLastRealOrderId());
        $payment = $order->getPayment();

        // Now let's get the Order's status from Optimal
        $client             = $this->_hostedClient;
        $orderStatus        = $this->_hosted->getOptimalOrderStatus($client, $optimalOrderId);
        $transaction        = $orderStatus->transaction;

        $customerSession = $this->_customerSession;

        if (isset($transaction->card->expiry)) {
            list($month, $year) = explode('/', $transaction->card->expiry);
        }


        $merchantCustomerId = $this->_coreSession->getOptimalAnonymousGeneratedCustomerId();

        if($merchantCustomerId) {
            $customerId = (int)$customerSession->getId();
            $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            $tableName = $this->_resource->getTableName('merchant_customer');
            $sql = " update {$tableName} set customer_id = {$customerId} where generated_merchant_id = '{$merchantCustomerId}' and customer_id = 0 limit 1";
            $connection->query($sql);

        }
        $this->_coreSession->unsOptimalAnonymousGeneratedCustomerId();
        if ($customerSession->isLoggedIn() && $transaction->paymentType != 'interac') {
            $customerId = $customerSession->getId();

            $customerData = $this->_customer->create()->load($customerId)->getData();
            $Card = $this->_creditcard->create();
            $digits = $transaction->card->lastDigits;

            $expiration = $month . '/' . substr($year, -2);
            $profile = $Card->getCollection()
                            ->addFieldToFilter('customer_id', $customerId)
                            ->addFieldToFilter('last_four_digits', $digits)
                            ->addFieldToFilter('card_expiration', $expiration)
                            ->getFirstItem();

            $merchantCustomerId = $this->_helper->getMerchantCustomerId($customerId);
            $merchantCustomerId = $merchantCustomerId['merchant_customer_id'];

            // this means the CC is not saved
            $profileDbId = $profile->getId();
            if (empty($profileDbId)) {
                $profile = $this->_creditcard->create();
            }

            // Set Profile Info
            $profile->setCustomerId($customerId);
            $profile->setProfileId($orderStatus->profile->id);

            $profile->setMerchantCustomerId($merchantCustomerId);
            $profile->setPaymentToken($orderStatus->profile->paymentToken);

            // Set Nickname
            $cardName = $orderStatus->transaction->card->brand;
            $profile->setCardNickname($this->_helper->processCardNickname($cardName));

            // Set Nickname
            $cardHolder = $customerData['firstname'] . ' ' . $customerData['lastname']; // $params['firstname'] . $params['lastname'];
            $profile->setCardHolder($cardHolder);

            // Set Card Info
            $profile->setLastFourDigits($transaction->card->lastDigits);

            $profile->setCardExpiration($expiration);

            $profile->save();
        }

        if (!isset($cardHolder)) {
            $cardHolder = $orderStatus->profile->firstName . ' ' . $orderStatus->profile->lastName;
        }

        $order->addStatusHistoryComment(
            'Netbanks Order Id: ' . $orderStatus->id .'<br/>' .
            'Reference: # ' . $orderStatus->merchantRefNum .'<br/>' .
            'Transaction Id: ' . $transaction->confirmationNumber .'<br/>' .
            'Status: ' . $transaction->status .'<br/>'
        );


        $payment->setStatus('APPROVED');

        $payment->setIsTransactionPending(false);

        $payment->setAdditionalInformation('order', serialize(array('id' => $optimalOrderId)));
        $payment->setAdditionalInformation('transaction', serialize($transaction));
        $payment->setTransactionId($optimalOrderId);
        // magento will automatically close the transaction on auth preventing the invoice from being captured online.
        $payment->setIsTransactionClosed(false);

        if ($transaction->paymentType != 'interac') {
            $payment->setCcOwner($cardHolder)
                ->setCcType($this->_helper->processCardNickname($transaction->card->brand))
                ->setCcExpMonth($month)
                ->setCcExpYear($year)
                ->setCcLast4($transaction->card->lastDigits);
        }

        $payment->save();


        $state = \Magento\Sales\Model\Order::STATE_NEW;
        if($this->_scopeConfig->getValue('payment/classyllama_llamacoin/payment_action',\Magento\Store\Model\ScopeInterface::SCOPE_STORE) == \ClassyLlama\LlamaCoin\Model\Config\Transaction::CAPT_VALUE) {
            $invoice = $order->prepareInvoice();
            $invoice->register();
            $invoice->setIsPaid(true);
            $order->addRelatedObject($invoice);
            $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
            //we need to save invoice?
        }

        $order->setState($state, true, "Invoice created.");
        $order->save();

        //$this->_redirect('checkout/onepage/success');
        $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/success');
            return $resultRedirect;
    }
}