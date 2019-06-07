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


class Plumrocket_Checkoutspage_PreviewController extends Mage_Core_Controller_Front_Action
{

    protected $_order;


    public function preDispatch()
    {
        parent::preDispatch();

        $_request = $this->getRequest();

        if ($_request->getParam('secret') != Mage::helper('checkoutspage')->getSecretKey()
            || !$this->_getOrder()
        ) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->_redirectUrl(Mage::getBaseUrl());
        } else if ($this->_getOrder()->getStoreId() != Mage::app()->getStore()->getStoreId()) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->_redirectUrl($this->getUrl('*/*/*', array(
                '_current' => true,
                '_store' => $this->_getOrder()->getStoreId()
            )));
        } else {
            $enabled = (bool)$this->getRequest()->getParam('new');
            Mage::app()->getStore()->setConfig('checkoutspage/general/enabled', $enabled);
            Mage::app()->getStore()->setConfig('checkoutspage/order_email/enabled', $enabled);
            Mage::app()->getStore()->setConfig(Mage_Checkout_Helper_Data::XML_PATH_GUEST_CHECKOUT, 1);
        }

        return $this;
    }



    public function pageAction()
    {
        Mage::register( 'turpentine_nocache_flag', true, true );

        $session = Mage::getSingleton('checkout/type_onepage')->getCheckout();


        $order = $this->_getOrder();
        $quoteId = $order->getQuoteId();
        $orderId = $order->getId();

        $session->setLastOrderId($orderId)
            ->setLastSuccessQuoteId($orderId)
            ->setLastQuoteId($quoteId)
            ->setLastOrderId($orderId);

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_forward('success', 'onepage', 'checkout', array('preview' => true));
    }


    public function emailAction()
    {
        Mage::register( 'turpentine_nocache_flag', true, true );

        $_helper = Mage::helper('checkoutspage');

        $newTemplate = (bool)$this->getRequest()->getParam('new');

        $order =  $this->_getOrder();
        $mailer = $order->getPMailer($newTemplate);
        $template = $order->getPTemplate($mailer);
        $vars = $mailer->getTemplateParams();


        if ($_helper->sendEmailHistoryEnabled()) {
            $vars['email_secret_link'] = Mage::getUrl('*/*/*',array('_current'=>true));
        }

        $this->getResponse()->setBody(
            $template->getProcessedTemplate($vars)
        );

    }


    protected function _getOrder()
    {
        if (is_null($this->_order)) {
            $incrementId = $this->getRequest()->getParam('order');
            if (!$incrementId) {
                $this->_order = false;
            } else {
                $this->_order = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('increment_id', $incrementId)
                    ->addFieldToFilter('store_id', Mage::app()->getStore()->getStoreId())
                    ->getFirstItem();

                if (!$this->_order->getId()) {
                    $this->_order = false;
                }
            }
        }

        return $this->_order;
    }


}
