<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

require_once Mage::getModuleDir('controllers', 'Mage_Customer').DS.'AccountController.php';

class Plumrocket_Checkoutspage_AccountController extends Mage_Customer_AccountController
{

    protected $_successMsg;

    public function subscribeCustomerAction()
    {

        if (!Mage::helper('checkoutspage')->moduleEnabled()){
            return $this->_sendJResponse('Module "Checkout Success Page" is disabled');
        }

        $_request = $this->getRequest();

        $session = Mage::getSingleton('customer/session');

        $order = Mage::helper('checkoutspage')->getOrder();

        if (!$order || !$order->getId() || !$this->getRequest()->isPost()) {
            return $this->_sendJResponse('There was a problem with the subscription.');
        }

        $websiteId = Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId();
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->loadByEmail($order->getCustomerEmail());

        if(!$customer->getId()) {
            return $this->_sendJResponse('There was a problem with the subscription.');
        }

        try {
            $customer->setIsSubscribed( true );
            $customer->save();
            return $this->_sendJResponse('Thank you for your subscription.', true);
        } catch (Exception $e) {
            return $this->_sendJResponse( $this->__('There was a problem with the subscription: %s', $e->getMessage()) );
        }
    }


    public function createCustomerAction()
    {
        if (!Mage::helper('checkoutspage')->moduleEnabled()){
            return $this->_forward('noRoute');
        }

        $_request = $this->getRequest();

        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            return $this->_sendJResponse('You are already logged in.');
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if (!$this->getRequest()->isPost()) {
            return $this->_sendJResponse('Cannot save the customer.');
        }

        $order = Mage::helper('checkoutspage')->getOrder();


        if (!$order || !$order->getId()) {
            return $this->_sendJResponse('Cannot save the customer.');
        }

        $billing    = $order->getBillingAddress();
        $shipping   = $order->canShip() ? null : $order->getShippingAddress();

        $websiteId = Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId();
        $customerId = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId)
            ->loadByEmail($order->getCustomerEmail())
            ->getId();

        if ($customerId) {
            $url = Mage::getUrl('customer/account/forgotpassword');
            return $this->_sendJResponse($this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url));
        }

        $customer = Mage::getModel('customer/customer');
        /** @var $customer Mage_Customer_Model_Customer */
        $customerBilling = $this->exportCustomerAddress($billing);
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $this->exportCustomerAddress($shipping);
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } elseif ($shipping) {
            $customerBilling->setIsDefaultShipping(true);
        }
        /**
         * @todo integration with dynamica attributes customer_dob, customer_taxvat, customer_gender
         */
        if ($order->getCustomerDob() && !$billing->getCustomerDob()) {
            $billing->setCustomerDob($order->getCustomerDob());
        }

        if ($order->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
            $billing->setCustomerTaxvat($order->getCustomerTaxvat());
        }

        if ($order->getCustomerGender() && !$billing->getCustomerGender()) {
            $billing->setCustomerGender($order->getCustomerGender());
        }

        if (!$order->getCustomerFirstname() && $billing->getFirstname()) {
            $order->addData(array(
                'customer_prefix' => $billing->getPrefix(),
                'customer_firstname' => $billing->getFirstname(),
                'customer_middlename' => $billing->getMiddlename(),
                'customer_lastname' => $billing->getLastname(),
                'customer_suffix' => $billing->getSuffix()
            ));
        }

        Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);
        $customer->setEmail($order->getCustomerEmail())
            ->setPrefix($order->getCustomerPrefix())
            ->setFirstname($order->getCustomerFirstname())
            ->setMiddlename($order->getCustomerMiddlename())
            ->setLastname($order->getCustomerLastname())
            ->setSuffix($order->getCustomerSuffix())
            ->setPassword($_request->getPost('password'))
            ->setConfirmation($_request->getPost('confirmation'))
            ->setPasswordConfirmation($_request->getPost('confirmation'))
            ->setPasswordHash($customer->hashPassword($customer->getPassword()));

        $errors = $customer->validate();

        if ($errors !== true) {
            foreach($errors as $error) {
                $this->_sendJResponse($error);
            }
            return;
        }

        if ($_request->getParam('is_subscribed', false)) {
            $customer->setIsSubscribed(1);
        }

        try {
            $errors = $this->_getCustomerErrors($customer);

            if (empty($errors)) {

                $customer->save();

                $this->_dispatchRegisterSuccess($customer);
                $this->_successProcessRegistration($customer);
            } else {
                $this->_addSessionError($errors);
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = $this->_getUrl('customer/account/forgotpassword');
                $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
            } else {
                $message = $this->_escapeHtml($e->getMessage());
            }
            return $this->_sendJResponse($message);
        } catch (Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            return $this->_sendJResponse($this->__('Cannot save the customer.' . $e));
        }

        $order
            ->setCustomerIsGuest(false)
            ->setCustomerGroupId($customer->getGroupId())
            ->setCustomer($customer)->save();

        //login customer
        $customer->setWebsiteId($websiteId);
        $session->setCustomerAsLoggedIn($customer);
        $session->setCustomer($customer);
        $session->renewSession();

        return $this->_sendJResponse($this->_successMsg, true);
    }

    /**
     * Success Registration
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     */
    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store*/
            $store = $app->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $customerHelper = $this->_getHelper('customer');
            $this->addSuccessMessage($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $url = $this->_getUrl('*/*/index', array('_secure' => true));
        } else {
            $session->setCustomerAsLoggedIn($customer);
            $this->_welcomeCustomer($customer);
        }

        return $this;
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->addSuccessMessage(
            $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType =  $this->_getHelper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
            }
            $this->addSuccessMessage($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );

        $successUrl = $this->_getUrl('*/*/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }


    public function exportCustomerAddress($a)
    {
        $address = Mage::getModel('customer/address');
        Mage::helper('core')->copyFieldset('sales_convert_quote_address', 'to_customer_address', $a, $address);
        return $address;
    }


    public function addSuccessMessage($message)
    {
        $this->_successMsg = $message;
        return $this;
    }

    protected function _sendJResponse($msg, $success = false)
    {
        $this->getResponse()->setBody(json_encode(array(
            'success' => $success,
            'message' => $this->__($msg),
        )));
        return $this;
    }


}