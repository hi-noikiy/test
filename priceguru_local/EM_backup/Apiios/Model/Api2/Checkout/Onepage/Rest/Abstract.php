<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Rest_Abstract extends Mage_Api2_Model_Resource
{
    protected $_outClassName = 'apiios/api2_checkout_onepage_output_abstract';
    protected $_stepCodes = array();
    protected $_customer;
	 protected $_sectionUpdateFunctions = array(
        'payment-method'  => '_getPaymentFields',
        'shipping_method' => '_getShippingMethods',
        'review'          => '_getReviewFields',
    );
	
    protected function _getCart()
    {
        return Mage::getSingleton('apiios/api2_cart')->setStore($this->_getStore());
    }

    public function  init() {
        if(!$this->_getCart()->getSummaryQty())
            return false;
        $this->_stepCodes = array(
            'login'             =>  'apiios/api2_checkout_onepage_output_login',
            'billing'           =>  'apiios/api2_checkout_onepage_output_billing',
            'shipping'          =>  'apiios/api2_checkout_onepage_output_shipping',
            'payment'           =>  'apiios/api2_checkout_onepage_output_payment',
            'review'            =>  'apiios/api2_checkout_onepage_output_review'
        );
        if(!Mage::registry('customer')){
            Mage::register('customer',  array(
                'customer'  =>  $this->getCustomer(),
                'type'      =>  $this->getUserType()
            ));
        }
        return true;
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

    public function getOutputByStep($step){
        return Mage::getSingleton($this->_stepCodes[$step])->setStore($this->_getStore());
    }

    protected function initCustomerSession(){
        return $this;
    }


    public function  _retrieve() {
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $this->initCustomerSession();
		//var_dump($this->init());exit;
        if($this->init()){
            $step = $this->getRequest()->getParam('step','login');
            $output = $this->getOutputByStep($step);
            if($method = $this->getRequest()->getParam('method')){
                $output->setData('method',$method);
            }
            return $output->toArrayFields();
        }
        return array(
            'messages'  =>  array(
                'error' =>  array(
                    'code'      =>  Mage_Api2_Model_Server::HTTP_OK,
                    'message'   =>  Mage::helper('apiios')->__('The cart is empty')
                )
            )
        );
    }

    public function getCheckoutMethodFields(){
        $helper = Mage::helper('apiios');
        return array(
            'paypal_express'    =>  $helper->__('Paypal Express'),
            'standard_checkout' =>  $helper->__('Standard Checkout')
        );
    }

    public function _getBillingFields(){
        $output = $this->getOutputByStep('billing');
        return $output->toArrayFields();
    }

    public function _getPaymentFields(){
        $output = $this->getOutputByStep('payment');
        return $output->toArrayFields();
    }

    public function _getShippingMethods(){
        $output = Mage::getSingleton('apiios/api2_checkout_onepage_output_method_available')->setStore($this->_getStore());
        return $output->toArrayFields();
    }

    public function _getShippingFields(){
        $output = $this->getOutputByStep('shipping');
        return $output->toArrayFields();
    }

    public function _getReviewFields(){
        $output = $this->getOutputByStep('review');
        return $output->toArrayFields();
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function  _saveCheckoutMethod($data) {
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $this->initCustomerSession();
        if (!empty ($data)) {
            if($this->init()){
                $step = $this->getRequest()->getParam('step','login');
                $segs = explode('_', $step);
                foreach ($segs as $i => $seg)
                    $segs[$i] = ucfirst(preg_replace('/([^A-Z])([A-Z])/', '$1_$2', $seg));
                $functionName = 'save'.implode('',$segs);
                $result = $this->$functionName($data);
                $this->_successMessage(
                    Mage::helper('apiios')->__('Progress is successful'),
                    Mage_Api2_Model_Server::HTTP_OK,
                    array('result'=>$result)
                );
            } else {
                $this->_errorMessage(
                    Mage::helper('apiios')->__('The cart is empty'),
                    Mage_Api2_Model_Server::HTTP_OK
                );
            }
        }
		
    }

	/**
     * Save Method Checkout. Method : POST. Ex Json params : {"method":"guest"}
     *
     * @param array $data
     */
	public function _create(array $data){
		$this->_saveCheckoutMethod($data);
		$this->_render($this->getResponse()->getMessages());
	}
	
	public function _multiCreate(array $data){
		$this->_saveCheckoutMethod($data[0]);
	}
	
    public function saveCheckoutMethod($dataSubmit){
        return array(
            'update_section'    =>  array(
                'name'  =>  'billing',
                'json_form' =>  $this->_getBillingFields()
            )
        );
    }

    /**
     * Save billing action. Method : POST
     *
     * @param array $dataSubmit
     * @return array
     */
    public function saveBilling($dataSubmit){
        if (!empty($dataSubmit)) {
//            $postData = $this->getRequest()->getPost('billing', array());
//            $data = $this->_filterPostData($postData);
            $data = $dataSubmit['billing'];
            $customerAddressId = isset($data['billing_address_id']) ? $data['billing_address_id'] : false;

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }

            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment',
                        'json_form' => $this->_getPaymentFields()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping_method',
                        'json_form' => $this->_getShippingMethods()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                    $result['update_section'] = array(
                        'name' => 'shipping',
                        'json_form'=> $this->_getShippingFields()
                    );
                }
            }

            return $result;
            //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping informaion save action
     *
     * @param array $dataSubmit
     * @return array
     */
    public function saveShipping($dataSubmit){
        if (!empty($dataSubmit)) {
            $data = $dataSubmit['shipping'];
            $customerAddressId = $data['shipping_address_id'];
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            if (!isset($result['error'])) {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'json_form' => $this->_getShippingMethods()
                );
            }
            return $result;
            //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping method save action. Method : POST, params : shipping_method[shipping_method] = string.
     *
     * @param array $dataSubmit
     * @return array
     */
    public function saveShippingMethod($dataSubmit)
    {
        if (!empty($dataSubmit)) {
            $data = $dataSubmit['shipping_method'];

            $result = $this->getOnepage()->saveShippingMethod($data);
            /*
            $result will have erro data if shipping method is empty
            */
            if(!$result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                    array('request'=>$this->getRequest(),
                        'quote'=>$this->getOnepage()->getQuote()));
                $this->getOnepage()->getQuote()->collectTotals();
                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'json_form' => $this->_getPaymentFields()
                );
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
            return $result;
        }
    }

    /**
     * Save payment action. Method : POST, params : payment[method] = string
     *
     * @param array $dataSubmit
     * @return array
     */
    public function savePayment($dataSubmit){
        // set payment to quote
        $result = array();
		
        if(!empty($dataSubmit)){
            try {
                $data = $dataSubmit['payment'];
                $result = $this->getOnepage()->savePayment($data);

                // get section and redirect data
                if (empty($result['error']) && !$redirectUrl) {
                    $result['goto_section'] = 'review';
                    $result['update_section'] = array(
                        'name' => 'review',
                        'json_form' => $this->_getReviewFields()
                    );
                }
            } catch (Mage_Payment_Exception $e) {
                if ($e->getFields()) {
                    $result['fields'] = $e->getFields();
                }
                throw new Mage_Api2_Exception($e->getMessage(),300);
            } catch (Mage_Core_Exception $e) {
                throw new Mage_Api2_Exception($e->getMessage(),300);
            } catch (Exception $e) {
                throw new Mage_Api2_Exception(Mage::helper('checkout')->__('Unable to set Payment Method.'),300);
            }
        }
        return $result;
    }
	
	/**
     * Create order. Method : POST, params : payment[method] = string
     *
     * @param array $dataSubmit
     * @return array
     */
    public function saveReview($dataSubmit){
		$result = array();
        try {
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
					throw new Mage_Api_Exception(Mage_Api2_Model_Server::HTTP_NOT_ACCEPTABLE, Mage::helper('checkout')->__('Please agree to all the terms and conditions before placing the order.'));
                }
            }
            if ($data = $dataSubmit['payment']) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }
			
            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if( !empty($message) ) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'json_form' => $this->_getPaymentFields()
            );
        } catch (Mage_Core_Exception $e) {
            //Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

            if ($updateSection = $this->getOnepage()->getCheckout()->getUpdateSection()) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'json_form' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            //Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            throw new Mage_Api2_Exception($e->getMessage(),300);
        }
        $this->getOnepage()->getQuote()->save();
		if (isset($redirectUrl)) {
			$result['redirect'] = $redirectUrl;
        }
		return $result;
        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
}
?>