<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_CustomerController extends Mage_Core_Controller_Front_Action
{
    private $_customerId = 0;
    /**
     * @var Mage_Customer_Model_Customer
     */
    private $_customer;

    protected $_title = null;

    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('amstcred')->isModuleActive()) {
            $this->norouteAction();
            return;
        }

        $session = Mage::getSingleton('customer/session');
        if (!$session->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        $this->_customerId = $session->getCustomer()->getId();
        $this->_customer = $session->getCustomer();
    }

    private function _renderLayoutWithMenu()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('amstcred/customer');
        }

        if (($headBlock = $this->getLayout()->getBlock('head')) && !is_null($this->_title)) {
            $headBlock->setTitle($this->_title);
        }
        $this->renderLayout();
    }

    public function indexAction()
    {
        $this->_title = $this->__('Store Credit');
        $this->_renderLayoutWithMenu();

    }

    public function subscribeAction()
    {
        $is_subscribe = (int)$this->getRequest()->getPost('amstcred_subscribe');
        Mage::getModel('amstcred/balance')->setCustomerId($this->_customerId)->loadByCustomer()->setSubscribeUpdates($is_subscribe)->save();

        if ($is_subscribe) {
            $message = 'Subscription successfully added';
        } else {
            $message = 'Subscription successfully deleted';
        }

        Mage::getSingleton('customer/session')->addSuccess($this->__($message));
        $this->_redirect('*/*/');
    }

    public function sendBalanceAction()
    {
        $data = $this->getRequest()->getPost('amstcred_send');

        try {
            $sendModel = Mage::getModel('amstcred/balanceSend');
            $sendModel->addData($data);
            $sendModel->setSender($this->_customer);
            $sendModel->send();
            Mage::getSingleton('customer/session')->addSuccess($this->__('Balance successfully send'));
            Mage::getSingleton('amstcred/session')->setSendFormData(false);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('customer/session')->addError($e->getMessage());
            Mage::getSingleton('amstcred/session')->setSendFormData($data);
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('Error, balance not send!'));
            Mage::getSingleton('customer/session')->addError($e->getMessage());
            Mage::getSingleton('amstcred/session')->setSendFormData($data);
        }

        $this->_redirect('*/*/');
    }

    public function removeCartAction()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote->getAmstcredUseCustomerBalance()) {
            Mage::getSingleton('checkout/session')->addSuccess($this->__('The store credit payment has been removed from shopping cart.'));
            $quote->setAmstcredUseCustomerBalance(false)->collectTotals()->save();
        } else {
            Mage::getSingleton('checkout/session')->addError($this->__('Store Credit payment is not being used in your shopping cart.'));
        }
        $place = $this->getRequest()->getParam('place', 'cart');

        if ($place == 'onepage') {
            $this->_redirect('checkout/onepage');
        } else {
            $this->_redirect('checkout/cart');
        }


    }

}
