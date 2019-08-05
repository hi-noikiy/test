<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Paysafe
 * @copyright   Copyright (c) 2017 Paysafe
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Paysafe\Paysafe\Controller;

class Payment extends \Magento\Framework\App\Action\Action
{
    protected $_quote = null;
    protected $_order = null;
    protected $_checkoutSession;
    protected $_localeResolver;
    protected $resultPageFactory;
    protected $_logger;
    protected $helperCore;
    protected $method;
    protected $information;
    protected $customer;
    protected $locale;
    protected $myPaymentInformationUrl = 'paysafe/payment/information';
    protected $recurringResponseUrl = 'paysafe/payment/recurringresponse';

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Paysafe\Paysafe\Logger\Logger $logger
     * @param \Paysafe\Paysafe\Helper\Core $helperCore
     * @param \Paysafe\Paysafe\Model\Payment\Information $information
     * @param \Paysafe\Paysafe\Model\Customer\Customer   $customer
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Paysafe\Paysafe\Logger\Logger $logger,
        \Paysafe\Paysafe\Helper\Core $helperCore,
        \Paysafe\Paysafe\Model\Payment\Information $information,
        \Paysafe\Paysafe\Model\Customer\Customer $customer,
        \Magento\Framework\Locale\ResolverInterface $locale
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_localeResolver = $localeResolver;
        $this->resultPageFactory = $resultPageFactory;
        $this->_logger = $logger;
        $this->helperCore = $helperCore;
        $this->information = $information;
        $this->customer = $customer;
        $this->locale = $locale;
    }

    /**
     * get checkout session
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * get Quote from checkout session
     *
     * @return \Magento\Sales\Model\Quote
     *
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * get last order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder()
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->load($this->_checkoutSession->getLastOrderId());

        $orderId = $order->getId();
        if (isset($orderId)) {
            return $order;
        }
        return null;
    }

    /**
     * get an order based on increment id
     * @param  string $incrementId
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrderByIncerementId($incrementId)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId($incrementId);

        return $order;
    }

    /**
     * execute Payment
     */
    public function execute()
    {
        $this->_logger->info('process generate payment form');
        $this->_order = $this->_getOrder();
        $this->method = $this->_order->getPayment()->getMethodInstance();

        $resultPage = $this->resultPageFactory->create();
        $this->addBreadCrumbs($resultPage);
        $this->setPageAsset($resultPage);

        $blockPaysafe = $resultPage->getLayout()->getBlock('paysafePaymentForm');
        $paymentResponseUrl = $this->_url->getUrl(
            'paysafe/payment/response',
            [
                'orderId' => $this->_order->getIncrementId(),
                '_secure' => true
            ]
        );
        $cancelUrl = $this->_url->getUrl('checkout/cart', ['_secure' => true]);

        $generalCredentials = $this->helperCore->getGeneralCredentials();
        $blockPaysafe->setBrand($this->method->getBrand());
        $blockPaysafe->setMerchantName($generalCredentials['merchant_name']);
        $blockPaysafe->setCaptureMethod($this->method->getSpecificConfiguration('capture_method'));
        $blockPaysafe->setApiKey($this->helperCore->getApiKey());
        $blockPaysafe->setEnvironment($this->method->getSpecificConfiguration('environment'));
        $blockPaysafe->setAmount(str_replace('.', '', number_format((float)$this->_order->getGrandTotal(), 2, '.', '')));
        $blockPaysafe->setCurrencyCode($this->_order->getOrderCurrencyCode());
        $blockPaysafe->setPaymentResponseUrl($paymentResponseUrl);
        $blockPaysafe->setCancelUrl($cancelUrl);

        $isRecurringActive = $this->helperCore->getGeneralCredentials()['recurring'];
        $blockPaysafe->setIsRecurringActive($isRecurringActive);
        $isActive = $this->method->getSpecificConfiguration('active');
        if ($isActive) {
            $blockPaysafe->setCustomerDataCreditCard(
                $this->information->getPaymentInformation($this->getCustomerId())
            );
        }

        return $resultPage;
    }

    /**
     * set a page asset
     * @param object $resultPageFactory
     */
    protected function setPageAsset($resultPageFactory)
    {
        $resultPageFactory->getConfig()->addPageAsset('Paysafe_Paysafe/css/payment_form.css');
    }

    /**
     * Add breadcrumbs
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return void
     */
    protected function addBreadCrumbs($resultPage)
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb(
            $this->method->getCode(),
            [
                'label' => $this->method->getTitle(),
                'title' => $this->method->getTitle()
            ]
        );
    }

    /**
     * redirect to checkout page when error or warning happen
     *
     * @param string $errorIdentifier
     * @param string $url
     * @return void
     *
     */
    protected function redirectError($errorIdentifier, $url = 'checkout/cart')
    {
        $this->messageManager->addError(__($errorIdentifier));
        $this->_redirect($url, ['_secure' => true]);
    }

    /**
     * deactive quote
     *
     * @return void
     */
    protected function deactiveQuote()
    {
        $quote = $this->_objectManager->create('Magento\Quote\Model\Quote');
        $quote->loadActive($this->_checkoutSession->getLastQuoteId());
        $quote->setReservedOrderId($this->_order->getIncrementId());
        $quote->setIsActive(false)->save();
    }

    /**
     * get payment parameters
     *
     * @param string $paymentToken
     * @return array
     */
    protected function getPaymentParameters($paymentToken)
    {
        $parameters = array();
        $billingAddress = $this->_order->getBillingAddress();

        $parameters['merchantRefNum'] = $this->_order->getIncrementId().time();
        $parameters['amount'] = str_replace('.', '', number_format((float)$this->_order->getGrandTotal(), 2, '.', ''));
        $parameters['settleWithAuth'] = (bool)$this->method->getSpecificConfiguration('settlement');
        $parameters['card']['paymentToken'] = $paymentToken;
        $parameters['billingDetails']['street'] = implode(' ', $billingAddress->getStreet());
        $parameters['billingDetails']['city'] = $billingAddress->getCity();
        $parameters['billingDetails']['state'] = $billingAddress->getRegionCode();
        $parameters['billingDetails']['country'] = $billingAddress->getCountryId();
        $parameters['billingDetails']['zip'] = $billingAddress->getPostcode();
        if($this->_order->getBillingAddress()->getTelephone()) {
            $parameters['billingDetails']['phone'] = $billingAddress->getTelephone();
        }

        return array_merge($parameters, $this->getCustomerInformationByOrder());
    }

     /**
     * get a customer information
     * @return array
     */
    protected function getCustomerInformationByOrder()
    {
        $customerInformation = array();
        $customerInformation['profile']['email'] = $this->_order->getBillingAddress()->getEmail();
        $customerInformation['profile']['firstName'] = $this->_order->getBillingAddress()->getFirstname();
        $customerInformation['profile']['lastName'] = $this->_order->getBillingAddress()->getLastname();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $remoteAddressObject = $objectManager->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        $customerInformation['customerIp'] = $remoteAddressObject->getRemoteAddress();

        return $customerInformation;
    }

    /**
     * create invoice
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     *
     */
    public function createInvoice($order)
    {
        $invoiceService = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService');

        $invoice = $invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(false);
        $invoice->getOrder()->setIsInProcess(true);

        $transactionSave = $this->_objectManager->create('Magento\Framework\DB\Transaction');
        $transactionSave->addObject($invoice)->addObject($invoice->getOrder())->save();

        $invoiceSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
        $invoiceSender->send($invoice);
    }

    /**
     * save an order additional information
     * @param  array $paymentResponse
     * @return void
     */
    public function saveOrderAdditionalInformation($paymentResponse)
    {
        $payment = $this->_order->getPayment();

        if (isset($paymentResponse['id'])) {
            $payment->setAdditionalInformation('TRANSACTION_ID', $paymentResponse['id']);
        }
        if (isset($paymentResponse['merchantRefNum'])) {
            $payment->setAdditionalInformation('MERCHANT_REFNUM', $paymentResponse['merchantRefNum']);
        }
        if (isset($paymentResponse['status'])) {
            $payment->setAdditionalInformation('AUTHORIZATION_STATUS', $paymentResponse['status']);
        }
        if (isset($paymentResponse['settlements'][0]['id'])) {
            $payment->setAdditionalInformation('SETTLEMENT_ID', $paymentResponse['settlements'][0]['id']);
        }
        if (isset($paymentResponse['settlements'][0]['status'])) {
            $payment->setAdditionalInformation('SETTLEMENT_STATUS', $paymentResponse['settlements'][0]['status']);
        }

        $this->_order->save();
    }

    /**
     * redirect to success page when update a payment status
     * @param  string $successIdentifier
     * @param  string $orderId
     * @param  string $url
     * @return void
     */
    public function redirectSuccessOrderDetail($successIdentifier, $orderId, $url = 'sales/order/view')
    {
        $this->messageManager->addSuccess(__($successIdentifier, $orderId));
        $this->_redirect($url, ['order_id' => (int)$orderId]);
    }

     /**
     * redirect to error page when update a payment status
     * @param  string  $errorMessage
     * @param  string  $orderId
     * @param  boolean|string $detailError
     * @param  string  $url
     * @return void
     */
    public function redirectErrorOrderDetail(
        $errorMessage,
        $orderId,
        $detailError = false,
        $url = 'sales/order/view')
    {
        if ($detailError) {
            $errorMessage .= ' : '.$detailError;
        }
        $this->messageManager->addError(__($errorMessage, $orderId));
        $this->_redirect($url, ['order_id' => (int)$orderId]);
    }

    /**
     * create a payment method object
     * @param  string $paymentMethod
     * @return object
     */
    public function createPaymentMethodObjectByPaymentMethod($paymentMethod)
    {
        $paymentMethodNameSpace = 'Paysafe\Paysafe\Model\Method\\'.$this->getPaymentMethodClassName($paymentMethod);
        return $this->_objectManager->create($paymentMethodNameSpace);
    }

    /**
     * get a payment method class name
     * @param  string $paymentMethod
     * @return string
     */
    public function getPaymentMethodClassName($paymentMethod)
    {
        $methods = array(
            'creditcard' => 'Creditcard'
        );

        $code = str_replace('paysafe_', '', $paymentMethod);

        if (isset($methods[$code])) {
            return $methods[$code];
        }

        return 'AbstractMethod';
    }

    /**
     * get customer id
     * @return array
     */
    public function getCustomerId()
    {
        $informationParameters = array();
        $informationParameters['customerId'] = $this->customer->getId();

        return $informationParameters;
    }

    /**
     * redirect to the payment error page at my payment information
     * @param  string $generalError
     * @param  string $detailError
     * @param  string $informationId
     * @param  string $url
     */
    protected function redirectErrorRecurring(
        $generalError,
        $detailError = null,
        $informationId = null
        )
    {
        if ($generalError) {
            $errorMessage = __($generalError);
        } elseif ($informationId) {
            $errorMessage = __('We are sorry. Your attempt to update your payment information was not successful.');
        } else {
            $errorMessage = __('We are sorry. Your attempt to save your payment information was not successful.');
        }
        if ($detailError) {
            $errorMessage .= ' : '.__($detailError);
        }
        $this->messageManager->addError($errorMessage);
        $this->_redirect($this->myPaymentInformationUrl, ['_secure' => true]);
    }

     /**
     * redirect to the register or change success page
     * @param  string $messageIdentifier
     * @param  string $url
     * @return string
     */
    protected function redirectSuccessRecurring($messageIdentifier)
    {
        $this->messageManager->addSuccess(__($messageIdentifier));
        $this->_redirect($this->myPaymentInformationUrl, ['_secure' => true]);
    }

    /**
     * get recurring parameters
     * @return array
     */
    protected function getRecurringParameters()
    {
        $recurringParameters = array();
        $recurringParameters = $this->customer->getCustomerInformation();
        switch ($this->locale->getLocale()) {
            case 'en_US':
            case 'en_GB':
            case 'fr_CA':
                $recurringParameters['locale'] = $this->locale->getLocale();
                break;
            default:
                $recurringParameters['locale'] = 'en_US';
                break;
        }
        return $recurringParameters;
    }
}
