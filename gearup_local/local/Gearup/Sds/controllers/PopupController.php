<?php

require_once 'Magestore/Sociallogin/controllers/PopupController.php';

class Gearup_Sds_PopupController extends Magestore_Sociallogin_PopupController {

    /**
     * @return mixed
     */
    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     *
     */
    public function loginAction()
    {
        //$sessionId = session_id();
        $username = $this->getRequest()->getPost('socialogin_email', false);
        $password = $this->getRequest()->getPost('socialogin_password', false);
        $currentStep = $this->getRequest()->getPost('currentCheckoutstep');
        $session = Mage::getSingleton('customer/session');

        $result = array('success' => false);
        $tmpIds = array();
        $popupItems = array();

        $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($username);
// Gets the current store's id
        if ($customer && $currentStep):
            $storeId = Mage::app()->getStore()->getStoreId();
            $quote = Mage::getModel('sales/quote')
                ->setSharedStoreIds($storeId)
                ->loadByCustomer($customer);

            if ($quote) {
                $collection = $quote->getItemsCollection();
                if ($collection->count() > 0)
                    foreach ($collection as $item)
                        $tmpIds[$item->getId()] = $item->getBuyRequest();
            }

        endif;


        if ($username && $password) {
            try {
                $session->login($username, $password);
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            if (!isset($result['error'])) {
                if (count($tmpIds) > 0):
                    $quote = Mage::getSingleton('checkout/cart')->getQuote();
                    foreach ($tmpIds as $k => $index):
                        $quote_item = Mage::getModel('sales/quote_item')->load($k);
                        if ($quote_item):
                            $buy_request = array_merge($index->getData(), array('product' => $quote_item->getProductId()));
                            $buy_request['form_key'] = Mage::getSingleton('core/session')->getFormKey();

                            /*limitation by stock when saving from 'later' to cart*/
                            $product = Mage::getModel('catalog/product')->load($buy_request['product']);
                            $disableMessage = '';


                            $savedQty = $buy_request['qty'];
                            if($quote_item->getRealProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) { //for bundle
                                $collection = $product->getTypeInstance(true)
                                    ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
                                foreach ($collection as $item) {
                                    $stockQty = $item->getStockItem()->getQty();
                                    if($savedQty > $stockQty && in_array($item->getSelectionId(), $buy_request['bundle_option'])) {
                                        $buy_request['qty'] = $stockQty;
                                        $buy_request['original_qty'] = $stockQty;
                                        $disableMessage .= "This product is currently out of stock. Please go back to '".$quote_item->getName()."' and choose different item to continue with your order<br>";
                                    }
                                }
                            } else { //for simples

                                $stockQty = $product->getStockItem()->getQty();

                                if ($savedQty > $stockQty || !$product->isSaleable()) {
                                    $buy_request['qty'] = $stockQty;
                                    $buy_request['original_qty'] = $stockQty;
                                    $disableMessage .= "This product is currently out of stock.";
                                }
                            }

                            $model = Mage::getModel('saveforlater/item')
                                ->setCustomerId($customer->getId())
                                ->setQuoteId($quote->getId())
                                ->setProductId($quote_item->getProductId())
                                ->setName($quote_item->getName())
                                ->setQty($quote_item->getQty())
                                ->setPrice($quote_item->getPrice())
                                //->setBuyRequest( serialize( $quote_item->getBuyRequest()->getData() ) )
                                ->setBuyRequest(serialize(array_merge($index->getData(), array('product' => $quote_item->getProductId()))))
                                ->setDateSaved(date('Y-m-d h:i:s', Mage::getModel('core/date')->timestamp()));

                            if($disableMessage) {
                                $model->setDisableMessage($disableMessage);
                            }


                            if ($model->getProductId()) { //filtering bundle options
                                $popupItems[] = $model;
                            }

                            try {
                                Mage::getSingleton('checkout/session')->getQuote()->removeItem($quote_item->getId())->save();
                            } catch (Exception $e) {
                                Mage::log($e->getMessage(), true, 'oye_step3.log');
                            }
                        endif;
                    endforeach;
                    Mage::getSingleton('core/session')->setPopupItems(serialize($popupItems));
                endif;
                $result['success'] = true;
            }
        } else {
            $result['error'] = $this->__(
                'Please enter a username and password.');
        }

        if ($currentStep == 'billing_shipping') {
            $methodInstance = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethod();
            if ($methodInstance != null) {
                Mage::getSingleton('core/session')->setData('customer_login', 1);
            }
        }
        $result['redirect'] = ($currentStep != '')? Mage::getUrl('checkout/onepage/index', array('goto'=>$currentStep)) : $this->_loginPostRedirect();
        //session_id($sessionId);
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     *
     */
    public function sendPassAction() {
        //$sessionId = session_id();
        /*$email = $this->getRequest()->getPost('socialogin_email_forgot', false);
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

        if ($customer->getId()) {
            try {
                $formId = 'social_user_forgot_password';
                $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
                if ($captchaModel->isRequired()) {
                    if (!$captchaModel->isCorrect($this->_getCaptchaString($this->getRequest(), $formId))) {
                        $result = array('success' => false, 'error' => Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
                        return $this->getResponse()->setBody(Zend_Json::encode($result));
                    }
                }
                $newPassword = $customer->generatePassword();
                $customer->changePassword($newPassword, false);
                $customer->sendPasswordReminderEmail();
                //Mage::getSingleton('core/session')->addNotice($this->__('If there is an account associated with ') . $email . $this->__(' you will receive an email with a link to reset your password.'));
                $result = array('success' => true, 'message' => "If there is an account associated with " . $email . " you will receive an email with a link to reset your password.");
            } catch (Exception $e) {
                $result = array('success' => false, 'error' => "Request Time out! Please try again.");
            }
        } else {
            $result = array('success' => false, 'error' => 'Your email address ' . $email . ' does not exist!');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result)); */

        $email = (string) $this->getRequest()->getPost('socialogin_email_forgot');
        if ($email) {

            $flowPassword = Mage::getModel('customer/flowpassword');
            $flowPassword->setEmail($email)->save();

            if (!$flowPassword->checkCustomerForgotPasswordFlowEmail($email)) {
                $result = array('success' => false, 'error' => "You have exceeded requests to times per 24 hours from 1 e-mail.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }

            if (!$flowPassword->checkCustomerForgotPasswordFlowIp()) {
                $result = array('success' => false, 'error' => "You have exceeded requests to times per hour from 1 IP.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }

            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $result = array('success' => false, 'error' => "Invalid email address.");
                $this->getResponse()->setBody(Zend_Json::encode($result));
                return;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                    $result = array('success' => true, 'message' => "If there is an account associated with " . $email . " you will receive an email with a link to reset your password.");

                } catch (Exception $exception) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/forgotpassword');
                    $result = array('success' => false, 'error' => $exception->getMessage());
                    $this->getResponse()->setBody(Zend_Json::encode($result));
                    return;
                }
            } else {
                $result = array('success' => false, 'error' => 'Your email address ' . $email . ' does not exist!');
            }

        } else {
            $result = array('success' => false, 'error' => 'Please enter your email.');
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
        return;
    }

    /**
     *
     */
    public function createAccAction() {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $result = array('success' => false, 'Can Not Login!');
        } else {
            $formId = 'social_user_create';
            $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
            if ($captchaModel->isRequired()) {
                if (!$captchaModel->isCorrect($this->_getCaptchaString($this->getRequest(), $formId))) {
                    $result = array('success' => false, 'error' => Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
                    return $this->getResponse()->setBody(Zend_Json::encode($result));
                }
            }
            $firstName = $this->getRequest()->getPost('firstname', false);
            $lastName = $this->getRequest()->getPost('lastname', false);
            $pass = $this->getRequest()->getPost('pass', false);
            $passConfirm = $this->getRequest()->getPost('passConfirm', false);
            $email = $this->getRequest()->getPost('email', false);
            $customer = Mage::getModel('customer/customer')
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setEmail($email)
                ->setPassword($pass)
                ->setConfirmation($passConfirm);

            try {
                $customer->save();
                Mage::dispatchEvent('customer_register_success', array('customer' => $customer)
                );
                if ($customer->isConfirmationRequired()) {
                    /** @var $app Mage_Core_Model_App */
                    $app = Mage::app();
                    /** @var $store  Mage_Core_Model_Store */
                    $store = $app->getStore();
                    $customer->sendNewAccountEmail(
                        'confirmation', $session->getBeforeAuthUrl(), $store->getId()
                    );
                    //$customerHelper = Mage::helper('customer');
                    $result = array('success' => false, 'error' => 'Account confirmation is required. Please, check your email for the confirmation link.');
                } else {
                    //$result = array('success' => true, 'redirect' => $this->_loginPostRedirect());
                    $result = array('success' => true, 'redirect' => Mage::app()->getStore()->getUrl('customer/account/login', array('_secure' => true)));
                    $methodInstance = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethod();
                    if($methodInstance != null){
                        Mage::getSingleton('core/session')->setData('customer_register',1);
                    }
                    $session->setCustomerAsLoggedIn($customer);
                }
                //$url = $this->_welcomeCustomer($customer);
                // $this->_redirectSuccess($url);
            } catch (Exception $e) {
                $result = array('success' => false, 'error' => $e->getMessage());
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    // copy to AccountController
    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return mixed
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false) {
        $this->_getSession()->addSuccess(
            $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();
            //$userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation', Mage::app()->getStore()->getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation', Mage::app()->getStore()->getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered', '', Mage::app()->getStore()->getId()
        );

        $successUrl = Mage::app()->getStore()->getUrl('customer/account/login', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * @param null $store
     * @return mixed
     */
    protected function _isVatValidationEnabled($store = null) {
        return Mage::helper('customer/address')->isVatValidationEnabled($store);
    }

    /**
     * @return mixed
     */
    protected function _loginPostRedirect() {
        $selecturl = Mage::getStoreConfig(('sociallogin/general/select_url'), Mage::app()->getStore()->getId());
        if ($selecturl == 0)
            return Mage::getUrl('customer/account');
        if ($selecturl == 2)
            return Mage::getUrl();
        if ($selecturl == 3)
            return Mage::getSingleton('core/session')->getSocialCurrentpage();
        if ($selecturl == 4)
            return Mage::getStoreConfig(('sociallogin/general/custom_page'), Mage::app()->getStore()->getId());
        if ($selecturl == 1 && Mage::helper('checkout/cart')->getItemsCount() != 0)
            return Mage::getUrl('checkout/cart');
        else
            return Mage::getUrl();
    }

}
