<?php
/**
 * @category    EM
 * @package     EM_Paypalexpress
 */
class EM_Apiios_Model_Api2_Paypalexpress_Rest_Abstract extends Mage_Api2_Model_Resource
{
    /**
     * @var Mage_Paypal_Model_Express_Checkout
     */
    protected $_checkout = null;

    /**
     * @var Mage_Paypal_Model_Config
     */
    protected $_config = null;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = false;

    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = 'paypal/config';

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = 'paypal/express_checkout';
    protected $_customer = null;

    public function init(){
        $this->_config = Mage::getModel($this->_configType, array($this->_configMethod));
        if(!Mage::registry('customer')){
            Mage::register('customer',  array(
                'customer'  =>  $this->getCustomer(),
                'type'      =>  $this->getUserType()
            ));
        }
        return $this;
    }

    public function _update($data){
        $this->init();
        $method = $this->getRequest()->getParam('step');
        $this->$method($data);
    }

    public function _retrieve(){
        $this->init();
        $method = $this->getRequest()->getParam('step');
        return $this->$method($data);
    }



    public function start($data){
        try {

            $this->_initCheckout();

            /*if ($this->_getQuote()->getIsMultiShipping()) {
                $this->_getQuote()->setIsMultiShipping(false);
                $this->_getQuote()->removeAllAddresses();
            }*/

            if($this->getUserType() == 'customer'){
                $customer = Mage::getModel('customer/customer')->setStoreId($this->_getStore()->getId())->load($this->getApiUser()->getUserId());
                $this->_checkout->setCustomerWithAddressChange(
                    $customer, $this->_getQuote()->getBillingAddress(), $this->_getQuote()->getShippingAddress()
                );
            }

            /*$customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer && $customer->getId()) {
                $this->_checkout->setCustomerWithAddressChange(
                    $customer, $this->_getQuote()->getBillingAddress(), $this->_getQuote()->getShippingAddress()
                );
            }*/

            // billing agreement
            //$isBARequested = (bool)$this->getRequest()
            //  ->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
            /*if ($customer && $customer->getId()) {
                $this->_checkout->setIsBillingAgreementRequested($isBARequested);
            }*/
            // giropay
            $this->_checkout->prepareGiropayUrls(
                Mage::getUrl('checkout/onepage/success'),
                Mage::getUrl('paypal/express/cancel'),
                Mage::getUrl('checkout/onepage/success')
            );

            $token = $this->_checkout->start($data['return_url'], $data['cancel_url']);

            if ($token) {
                $this->_initToken($token);
                $this->_successMessage(
                    Mage::helper('customer')->__('Get token paypal success'),
                    Mage_Api2_Model_Server::HTTP_OK,
                    array('result'=>array('token'=>$token))
                );

                $this->_render($this->getResponse()->getMessages());
            }
        } catch (Mage_Core_Exception $e) {
            throw new Mage_Api2_Exception($e->getMessage(),300);
        } catch (Exception $e) {
            throw new Mage_Api2_Exception(Mage::helper('paypal')->__('Unable to start Express Checkout.'),300);
        }


    }

    public function returnPaypal()
    {
        $this->_initCheckout();
        $this->_checkout->returnFromPaypal($this->_initToken());
        return $this->loadReviewFields();
    }

    public function loadReviewFields(){
        $shipping = Mage::getModel('apiios/api2_paypalexpress_output_shipping')->setStore($this->_getStore());
        $billing = Mage::getModel('apiios/api2_paypalexpress_output_billing')->setStore($this->_getStore());
//        print_r($billing->getAddress()->getData());
        //print_r($shipping->getAddress()->getData());
//exit;
        $customerEmailField = array();
        if(!$this->getCustomer()){
            $customerEmailField['label'] = Mage::helper('checkout')->__('Email Address');
            $customerEmailField['required'] = true;
            $customerEmailField['name'] = 'customer-email';
            $customerEmailField['type'] = 'text';
            $customerEmailField['value'] = $billing->getAddress()->getEmail();
        }
        return array(
            'result'=> array(
                'customer-email'=>  $customerEmailField,
                'billing'       =>  $billing->loadFields(),
                'shipping'      =>  $shipping->loadFields(),
                'totals'	=>  Mage::getModel('apiios/api2_cart')->setStore($this->_getStore())->totals(),
                'shipping_method'   =>  Mage::getSingleton('apiios/api2_paypalexpress_output_shipping_method')->setStore($this->_getStore())->toArrayFields()
            )
        );
    }

    /**
     * Update shipping method selected
     *
     * @param $data
     * @return array shipping method available
     */
    public function loadFieldShippingMethods($data){
        try {
            $this->_initCheckout();
            $this->_checkout->prepareOrderReview($this->_initToken());
            $output = Mage::getSingleton('apiios/api2_paypalexpress_output_shipping_method')->setStore($this->_getStore());
            return $output->toArrayFields();
            //$this->_render($this->getResponse()->getMessages());
        } catch (Mage_Core_Exception $e) {
            throw new Mage_Api2_Exception($e->getMessage(),300);
        } catch (Exception $e) {
            throw new Mage_Api2_Exception(Mage::helper('paypal')->__('Unable to update Order data.'),300);
        }
    }

    /**
     * Update order detail
     *
     * @method PUT
     * @param $data
     * @return array
     */
    public function updateOrder($data){
        try {
            $this->_initCheckout();
            $this->_checkout->updateOrder($data);
            $result = array();
            $result['totals'] = Mage::getModel('apiios/api2_cart')->setStore($this->_getStore())->totals();
            if(!isset($data['shipping_method'])){
                $result['shipping_method'] = $this->loadFieldShippingMethods($data);
            }
            $this->_successMessage(
                Mage::helper('apiios')->__('Update order successful'),
                Mage_Api2_Model_Server::HTTP_OK,
                array('result'=>$result)
            );
            $this->_render($this->getResponse()->getMessages());
        } catch (Mage_Core_Exception $e) {
            throw new Mage_Api2_Exception($e->getMessage(),300);
        } catch (Exception $e) {
            throw new Mage_Api2_Exception(Mage::helper('paypal')->__('Unable to update Order data.'),300);
        }
    }

    public function placeOrder($data){
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                if(isset($data['agreement']))
                    $postedAgreements = array_keys($data('agreement'));
                else
                    $postedAgreements = array();
                if (array_diff($requiredAgreements, $postedAgreements)) {
                    throw new Mage_Api2_Exception(Mage::helper('paypal')->__('Please agree to all the terms and conditions before placing the order.'),300);
                }
            }

            $this->_initCheckout();
            $this->_checkout->place($this->_initToken());

            // prepare session to success or cancellation page
            $session = $this->_getCheckoutSession();
            $session->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
                // as well a billing agreement can be created
                $agreement = $this->_checkout->getBillingAgreement();
                if ($agreement) {
                    $session->setLastBillingAgreementId($agreement->getId());
                }
            }

            // recurring profiles may be created along with the order or without it
            $profiles = $this->_checkout->getRecurringPaymentProfiles();
            if ($profiles) {
                $ids = array();
                foreach($profiles as $profile) {
                    $ids[] = $profile->getId();
                }
                $session->setLastRecurringProfileIds($ids);
            }

            $this->_initToken(false); // no need in token anymore
            $this->_successMessage(
                Mage::helper('apiios')->__('Place order successful'),
                Mage_Api2_Model_Server::HTTP_OK,
                array('result'=>array('increment_id'=>$session->getLastRealOrderId()))
            );
            $this->_render($this->getResponse()->getMessages());
        }
        catch (Mage_Core_Exception $e) {
            throw new Mage_Api2_Exception($e->getMessage(),300);
        }
        catch (Exception $e) {
            throw new Mage_Api2_Exception(Mage::helper('paypal')->__('Unable to place the order.'),300);
        }
    }

    /**
     * Instantiate quote and checkout
     * @throws Mage_Core_Exception
     */
    private function _initCheckout()
    {
        //$cart = $this->_getCart();
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            //$this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            Mage::throwException(Mage::helper('paypal')->__('Unable to initialize Express Checkout.'));
        }

        $this->_checkout = Mage::getSingleton($this->_checkoutType, array(
            'config' => $this->_config,
            'quote'  => $quote,
        ));
    }

    /**
     * Search for proper checkout token in request or session or (un)set specified one
     * Combined getter/setter
     *
     * @param string $setToken
     * @return Mage_Paypal_ExpressController|string
     */
    protected function _initToken($setToken = null)
    {
        if (null !== $setToken) {
            if (false === $setToken) {
                // security measure for avoid unsetting token twice
                if (!$this->_getSession()->getExpressCheckoutToken()) {
                    Mage::throwException(Mage::helper('paypal')->__('PayPal Express Checkout Token does not exist.'));
                }
                $this->_getSession()->unsExpressCheckoutToken();
            } else {
                $this->_getSession()->setExpressCheckoutToken($setToken);
            }
            return $this;
        }
        if ($setToken = $this->getRequest()->getParam('token')) {
            if ($setToken !== $this->_getSession()->getExpressCheckoutToken()) {
                Mage::throwException(Mage::helper('paypal')->__('Wrong PayPal Express Checkout Token specified.'));
            }
        } else {
            $setToken = $this->_getSession()->getExpressCheckoutToken();
        }
        return $setToken;
    }

    /**
     * PayPal session instance getter
     *
     * @return Mage_PayPal_Model_Session
     */
    private function _getSession()
    {
        return Mage::getSingleton('paypal/session');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Return cart object
     *
     * @return EM_Apiios_Model_Api2_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('apiios/api2_cart')->setStore($this->_getStore());
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
//    	return Mage::getSingleton('checkout/session');
        return Mage::getSingleton('apiios/api2_checkout_session')->setStore($this->_getStore());
    }

    /**
     * Get Current Customer
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer(){
        if(!$this->_customer)
            $this->_customer = Mage::getModel('customer/customer')->setStoreId($this->_getStore()->getId())->load($this->getApiUser()->getUserId());
        return $this->_customer;
    }

}