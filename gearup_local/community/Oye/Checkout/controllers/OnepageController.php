<?php

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'OnepageController.php';

class Oye_Checkout_OnepageController extends Mage_Checkout_OnepageController {

    public function preDispatch() {
//        if (!$this->_isOneStepCheckoutLayout()) {
//            return parent::preDispatch();
//        }
        parent::preDispatch();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (!$quote->getShippingAddress()->getCollectShippingRates()) {
//            $this->getOnepage()->saveCheckoutMethod('register');
            $quote->getShippingAddress()->setSameAsBilling(1);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            if (!$quote->getShippingAddress()->getCountryId()) {
                $quote->getShippingAddress()->setCountryId(Mage::helper('core')->getDefaultCountry());
            }
        }
        if ($messages = $this->getRequest()->getParam('giftmessage', array())) {
            $this->getOnepage()->createGiftMessage($messages);
        }
    }

    public function getUpdateHtml($update) {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load($update);
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

    public function postDispatch() {
        parent::postDispatch();
        if (!$this->_isOneStepCheckoutLayout()) {
            return $this;
        }
        if (!$this->getRequest()->getHeader('X-Requested-With')) {
            return $this;
        }
        $result = array('blocks' => array());
        if ($blocks = $this->getRequest()->getParam('blocks', array())) {
            $blocks = explode(',', $blocks);
        }
        $body = Mage::helper('core')->jsonDecode($this->getResponse()->getBody());
        if (isset($body['message'])) {
            $result['error'] = $body['message'];
        }
        if (isset($body['success'])) {
            $result['success'] = $body['success'];
        }
        if (isset($body['redirect'])) {
            $result['redirect'] = $body['redirect'];
        }
        if (Mage::helper('oyecheckout')->isOneStepLayout()) {
            $this->getLayout()->getUpdate()->merge('oyecheckout_onepage_index');
        }


        $this->getLayout()->generateXml();
        $this->getLayout()->generateBlocks();
        $onepageBlock = $this->getLayout()->getBlock('custom.checkout.onepage');
        foreach ($blocks as $blockName) {
            $result['blocks'][$blockName] = $onepageBlock->getChild($blockName)->toHtml();
        }
        echo Mage::helper('core')->jsonEncode($result);
        exit;
    }

    public function indexAction() {
//        if (!$this->_isOneStepCheckoutLayout()) {
//            return parent::indexAction();
//        }

        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems()) {  // || $quote->getHasError() was before. Loop issue on checkout when product is out of stock.
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                    Mage::getStoreConfig('sales/minimum_order/error_message') :
                    Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        //$quote->getShippingAddress()->setCountryId(Mage::helper('core')->getDefaultCountry());
        //Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
        $this->getOnepage()->initCheckout();
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();


        if (Mage::helper('oyecheckout')->isStandartLayout()) {
            $update->addHandle('checkout_onepage_index');
        } elseif (Mage::helper('oyecheckout')->isHorisontalLayout()) {
            $update->addHandle('oyecheckout_horizontal');
        } elseif (Mage::helper('oyecheckout')->isOneStepLayout()) {
            $update->addHandle('oyecheckout_onepage_index');
        }

        $this->loadLayoutUpdates();
        $this->generateLayoutXml();
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('wishlist/session');
        $this->renderLayout();
    }

    public function progressAction() {
        // previous step should never be null. We always start with billing and go forward
        $prevStep = $this->getRequest()->getParam('prevStep', false);

        if ($this->_expireAjax() || !$prevStep) {
            return null;
        }

        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        /* Load the block belonging to the current step */
        $update->load('oyecheckout_horizontal_progress_' . $prevStep);
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        $this->getResponse()->setBody($output);
        return $output;
    }
    
    /**
     * Get order review step html
     *
     * @return string
     */
    protected function _getReviewHtml()
    {
        return $this->getLayout()->getBlock('root2')->toHtml();
    }
    
    public function savePaymentAction() {
//                $this->loadLayout('checkout_onepage_review_horizontal');        
//        echo $this->_getReviewHtml();exit;
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            // set payment to quote
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review_horizontal');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function getUpdatedCartSummeryAction() {
        $this->_saveShippingMethod();
        $this->_savePayment();
//        $layout = $this->getLayout();
//        $update = $layout->getUpdate();
        Mage::getSingleton('checkout/cart')->getQuote()->collectTotals()->save();
        /* Load the block belonging to the current step */
//        $update->load('oyecheckout_horizontal_progress_cart');
//        $layout->generateXml();
//        $layout->generateBlocks();
//        $output = $layout->getOutput();
//        $this->getResponse()->setBody($output);
//        return $output;
    }
    
    public function ajaxAction(){
        $layout = $this->getLayout();
        $update = $layout->getUpdate();    
        /* Load the block belonging to the current step */
//        $update->load('oyecheckout_horizontal_cart');
//        $layout->generateXml();
//        $layout->generateBlocks();
//        $output = $layout->getOutput();
//        $this->getResponse()->setBody($output);
//        return $output;
    }

    public function saveOrderAction() {
        if (!$this->_isOneStepCheckoutLayout()) {
            parent::saveOrderAction();
            return;
        }
        if ($this->_expireAjax()) {
            return;
        }

        $this->_applyCheckoutMethod();

        $result = array();
        try {

            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['message'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            if ($error = $this->_saveBilling()) {
                $result['success'] = false;
                $result['error'] = true;
                $result['message'] = $error;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            } elseif ($error = $this->_saveShipping()) {
                $result['success'] = false;
                $result['error'] = true;
                $result['message'] = $error;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            } elseif ($error = $this->_saveShippingMethod()) {
                $result['success'] = false;
                $result['error'] = true;
                $result['message'] = $error;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }

            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
                $result['success'] = true;
                $result['error'] = false;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }

            $this->getOnepage()->saveOrder();
            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['message'] = $message;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['message'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['message'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    
    
      /**
     * Save checkout billing address
     */
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationOnCheckoutEnabled() && !$this->_validateFormKey()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            if(isset($data['checkout_method']) && !Mage::getSingleton('customer/session')->isLoggedIn() ){
                 $this->getOnepage()->saveCheckoutMethod($data['checkout_method']);
            }
           
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
               $this->loadLayout('checkout_onepage_review_horizontal');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }

            $this->_prepareDataJSON($result);
        }
    }
    
    
     /**
     * Shipping address save action
     */
    public function saveShippingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->isFormkeyValidationOnCheckoutEnabled() && !$this->_validateFormKey()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            
            if (strpos($data['firstname'], ' ') !== false) {
               
                $fullname = explode(' ',$data['firstname']);
                $data['firstname'] = $fullname[0];
                $data['lastname'] = $fullname[1];
            }
            if(!isset($data['differet_billing'])){
                //$data['use_for_shipping'] = 1;
                $_POST['billing'] = $data;
                if(empty($data['customer_password']))
                    $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
                $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
                $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
            }else
              $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
             
            if(isset($data['event_country_change'])){               
                if (isset($data['country_id'])) {
                    $address =$this->getOnepage()->getQuote()->getShippingAddress();                    
                    $this->getOnepage()->getQuote()->getShippingAddress()->setData('country_id', $data['country_id'])->save();
                    $address->setCollectShippingRates(true);
                    $address->save();
                    $this->getOnepage()->getQuote()->collectTotals()->save();
                 }
                unset($result['error']);
                $this->_prepareDataJSON($result);
                return;
            }
            if (!isset($result['error'])) {
                //$this->loadLayout('checkout_onepage_review_horizontal');
                //$result['goto_section'] = 'review';
                $result['duplicateShippingInfo'] = true;
                $this->loadLayout('checkout_onepage_review_horizontal');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
                //$result['duplicateBillingInfo'] = 'true';
            }
            $this->_prepareDataJSON($result);
        }
    }
    
    protected function _saveBilling() {
        $data = $this->getRequest()->getPost('billing', array());
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }
        $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

        return isset($result['error']) ? $result['message'] : '';
    }

    protected function _saveShipping() {
        if (Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getSameAsBilling() || $this->getOnepage()->getQuote()->isVirtual()
        ) {
            return '';
        }
        $data = $this->getRequest()->getPost('shipping', array());
        $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
        $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
        return isset($result['error']) ? $result['message'] : '';
    }

    protected function _saveShippingMethod() {
        if ($this->getOnepage()->getQuote()->isVirtual()) {
            return '';
        }
        $data = $this->getRequest()->getPost('shipping_method', '');
        $result = $this->getOnepage()->saveShippingMethod($data);
        return isset($result['error']) ? $result['message'] : '';
    }

    protected function _savePayment() {
        $data = $this->getRequest()->getPost('payment', array());
        $result = $this->getOnepage()->savePayment($data);
        return isset($result['error']) ? $result['message'] : '';
    }

    protected function _isOneStepCheckoutLayout() {
        return Mage::helper('oyecheckout')->isOneStepLayout();
    }

    public function renderLayout($output = '') {
        if (in_array('checkout_onepage_index', $this->getLayout()->getUpdate()->getHandles())) {
            if (Mage::helper('oyecheckout')->isHorisontalLayout()) {
                if ($head = $this->getLayout()->getBlock('head')) {
                    $head->addItem('skin_css', 'css/oyecheckout/horizontal.css');
                    if (Mage::helper('oyecheckout')->isResponsive()) {
                        $head->addItem('skin_css', 'css/oyecheckout/horizontal-responsive.css');
                    }
                }
//                $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
            } elseif (Mage::helper('oyecheckout')->isStandartLayout()) {
                if ($head = $this->getLayout()->getBlock('head')) {
                    $head->addItem('skin_css', 'css/oyecheckout/standard.css');
                }
                if (Mage::helper('oyecheckout')->isResponsive()) {
                    $head->addItem('skin_css', 'css/oyecheckout/standard-responsive.css');
                }
            }
        }
        return parent::renderLayout($output);
    }

    function couponAction() {

        $this->loadLayout('checkout_onepage_review_horizontal');
//        if (Mage::helper('oyecheckout')->isHorisontalLayout()) {
//
//        } else {
//            $this->loadLayout('checkout_onepage_review');
//        }

        $this->couponCode = (string) $this->getRequest()->getParam('coupon_code');

        Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->setCollectShippingRates(true);

        Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode(strlen($this->couponCode) ?
                        $this->couponCode : ' ')->collectTotals()->save();

        $result['goto_section'] = 'review';

        $result['update_section'] = array('name' => 'review', 'html' => $this->_getReviewHtml());


        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Used for oneStepCheckout layout only
     * other checkout layouts save checkout method with separate request
     */
    private function _applyCheckoutMethod() {
        if (!Mage::helper('checkout')->isAllowedGuestCheckout($this->getOnepage()->getQuote()) || Mage::getSingleton('customer/session')->isLoggedIn()) {
            return;
        }

        $billingData = Mage::app()->getRequest()->getPost('billing');
        if (!isset($billingData['register'])) {
            $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
        } else {
            $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
        }
    }
    /**
     * AJAX check if email exist, if exist display popup.
     * @return type
     */
    public function checkEmailexistsAction() {

        if ($this->_expireAjax()) {
            echo 'Ajax Expired';
            return;
        }
       
        $websiteId = Mage::app()->getWebsite()->getId();
        $email = $this->getRequest()->getParam('email', array());


        $customer = Mage::getModel('customer/customer');

        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        $result['status'] = $customer->getId()?true:false;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
     public function shippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        echo $this->_getShippingMethodsHtml();exit;
      
    }

}
