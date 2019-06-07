<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * User login
     */
    public function checkloginAction() {
        $params = $this->getRequest()->getParams();
        if (isset($params['login']['username']) && isset($params['login']['password'])) {
            /** @var Mage_Customer_Model_Session $customerSession */
            $customerSession = Mage::getSingleton('customer/session');
            try {
                $customerSession->login($params['login']['username'], $params['login']['password']);
                $result['form_key'] = Mage::getSingleton('core/session')->getData('_form_key');
            } catch (Exception $e) {
                $result['data'] = $e->getMessage();
                $this->getResponse()->setBody(json_encode($result));
                return;
            }
            $result['data'] = '1';
            $this->getResponse()->setBody(json_encode($result));
            return;
        }
        $result['data'] = Mage::helper('detailedreview')->__('Please, fill in Email and Password.');
        $this->getResponse()->setBody(json_encode($result));
        return;
    }

    /**
     * User registration
     */
    public function registrationAction()
    {
        $params = $this->getRequest()->getParams();

        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $this->getResponse()->setBody('1');
            return;
        }
        $errors = array();

        $helper = Mage::helper('detailedreview');
//        if (Mage::getStoreConfig("fontis_recaptcha/recaptcha/customer"))
//        { // check that recaptcha is actually enabled
//            $privatekey = Mage::getStoreConfig("fontis_recaptcha/setup/private_key");
//            // check response
//            $resp = Mage::helper("fontis_recaptcha")->recaptcha_check_answer(  $privatekey,
//                $_SERVER["REMOTE_ADDR"],
//                $params["recaptcha_challenge_field"],
//                $params["recaptcha_response_field"]
//            );
//            if ($resp != true)
//            { // if recaptcha response is correct, use core functionality
//                $message = array('error' => $helper->__('Your reCAPTCHA entry is incorrect. Please try again.'));
//                $this->getResponse()->setBody(json_encode($message));
//                return;
//            }
//        }

        if (!$customer = Mage::registry('current_customer')) {
            $customer = Mage::getModel('customer/customer')->setId(null);
        }

        /* @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer);

        $customerData = $customerForm->extractData($this->getRequest());

        if ($this->getRequest()->getParam('is_subscribed', false)) {
            $customer->setIsSubscribed(1);
        }

        // Initialize customer group id
        $customer->getGroupId();

        try {
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                $customer->setPassword($params['password']);
                $customer->setConfirmation($params['confirmation']);
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($customerErrors, $errors);
                }
            }

            if (!count($errors)) {
                $customer->save();

                Mage::dispatchEvent('customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );

                if ($customer->isConfirmationRequired()) {
                    $customer->sendNewAccountEmail(
                        'confirmation',
                        $session->getBeforeAuthUrl(),
                        Mage::app()->getStore()->getId()
                    );
                    $result = array('success' => 'Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()));
                    $this->getResponse()->setBody(json_encode($result));
                    return;
                } else {
                    $session->setCustomerAsLoggedIn($customer);
                    $this->getResponse()->setBody('1');
                    return;
                }
            } else {
                $this->getResponse()->setBody($helper->__('Invalid customer data'));
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = Mage::getUrl('customer/account/forgotpassword', array( '_secure' => true));
                $message = $helper->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $this->getResponse()->setBody(json_encode(array('error' => $message)));
        } catch (Exception $e) {
            $this->getResponse()->setBody($helper->__('Can not save the customer.'));
        }
    }

    /**
     * Set user timezone on first visit
     */
    public function setTimezoneAction()
    {
        Mage::getModel('customer/session')->setClientTimezone($this->getRequest()->getParam('timezone'));
    }

    public function submitPageAction()
    {
        if (Mage::helper('detailedreview/config')->isSeparateForm() &&
            $productId = (int) Mage::app()->getRequest()->getParam('product')) {
            if(!$product = Mage::registry('current_product')) {
                $product = Mage::getModel('catalog/product')->load($productId);
                Mage::register('current_product', $product);
            }
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_forward('defaultNoRoute');
        }
    }
}
