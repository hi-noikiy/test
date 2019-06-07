<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */
class Magestore_Sociallogin_PopupController extends Mage_Core_Controller_Front_Action
{


    /**
     * @return mixed
     */
    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     *
     */
    public function loginAction() {
        if (!$this->_validateFormKey()) {
            $result = array(
                'success' => false,
                'error' => $this->__('Invalid Form Key')
            );
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }else{
            $username = $this->getRequest()->getPost('socialogin_email', false);
            $password = $this->getRequest()->getPost('socialogin_password', false);
            $session = Mage::getSingleton('customer/session');

            $result = array('success' => false);

            if ($username && $password) {
                try {
                    $session->login($username, $password);
                } catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
                if (!isset($result['error'])) {
                    $result['success'] = true;
                }
            } else {
                $result['error'] = $this->__(
                    'Please enter a username and password.');
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    /**
     *
     */
    public function sendPassAction() {
        //$sessionId = session_id();
        $email = $this->getRequest()->getPost('socialogin_email_forgot', false);
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);

        if ($customer->getId()) {
            try {
                $newPassword = $customer->generatePassword();
                $customer->changePassword($newPassword, false);
                $customer->sendPasswordReminderEmail();
                Mage::getSingleton('core/session')->addNotice($this->__('If there is an account associated with ') . $email . $this->__(' you will receive an email with a link to reset your password.'));
                $result = array('success' => true, 'message' => "If there is an account associated with " . $email . " you will receive an email with a link to reset your password.");
            } catch (Exception $e) {
                $result = array('success' => false, 'error' => "Request Time out! Please try again.");
            }
        } else {
            $result = array('success' => false, 'error' => 'Your email address ' . $email . ' does not exist!');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }


    protected function _getCaptchaString($request, $formId)
    {
        $captchaParams = $request->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);
        return $captchaParams[$formId];
    }

    /**
     *
     */
    public function createAccAction() {
        if (!$this->_validateFormKey()) {
            $result = array(
                'success' => false,
                'error' => $this->__('Invalid Form Key')
            );
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }else{
            $formId = 'social_user_create';
            $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
            if ($captchaModel->isRequired()) {
                if (!$captchaModel->isCorrect($this->_getCaptchaString($this->getRequest(), $formId))) {
                    $result = array('success'=>false, 'error'=>Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
                    return $this->getResponse()->setBody(Zend_Json::encode($result));
                }
            }
            $session = Mage::getSingleton('customer/session');
            if ($session->isLoggedIn()) {
                $result = array('success' => false, $this->__('Can Not Login!'));
            } else {
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
                    Mage::dispatchEvent('customer_register_success',
                        array('customer' => $customer)
                    );
                    if ($customer->isConfirmationRequired()) {
                        /** @var $app Mage_Core_Model_App */
                        $app = Mage::app();
                        /** @var $store  Mage_Core_Model_Store */
                        $store = $app->getStore();
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            $session->getBeforeAuthUrl(),
                            $store->getId()
                        );
                        $result = array(
                            'success' => false, 
                            'error' => $this->__('Account confirmation is required. Please, check your email for the confirmation link.')
                        );
                    } else {
                        $result = array('success' => true);
                        $session->setCustomerAsLoggedIn($customer);
                    }
                } catch (Exception $e) {
                    $result = array('success' => false, 'error' => $e->getMessage());
                }
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
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
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
}