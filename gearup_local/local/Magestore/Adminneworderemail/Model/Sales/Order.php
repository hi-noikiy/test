<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magestore.com/MageStore_License.txt
 *
 * @category   Magestore
 * @package    Magestore_OnepageCheckout
 * @copyright  Copyright (c) 2009 MageStore. (http://www.magestore.com)
 * @license    http://www.magestore.com/MageStore_License.txt
 */
class Magestore_Adminneworderemail_Model_Sales_Order extends Mage_Sales_Model_Order {

    const XML_PATH_ADMIN_EMAIL_TEMPLATE = 'sales_email/order/admin_email_template';

    public function sendAdminOrderNotification($orderId) {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        $mailTemplate = Mage::getModel('core/email_template');
        $template = Mage::getStoreConfig(self::XML_PATH_ADMIN_EMAIL_TEMPLATE, $this->getStoreId());
        $adminEmail = '';
        $adminName = '';
        $_order = Mage::getModel('sales/order')->load($orderId);
        $paymentBlock = Mage::helper('payment')->getInfoBlock($_order->getPayment())
                ->setIsSecureMode(true);

        $paymentBlock->getMethod()->setStore($_order->getStore()->getId());

        //echo $_order->getBaseGrandTotal() . '<br/>';


        $adminEmailString = Mage::getStoreConfig('sales_email/order/admin_email_notify');

        $adminEmailArray = explode(',', $adminEmailString);

        foreach ($adminEmailArray as $adminEmail) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
                ->sendTransactional(
                        $template, Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $this->getStoreId()), $adminEmail, $adminName, array(
                    'order' => $_order,
                    'payment_html' => $paymentBlock->toHtml(),
                )
            );
        }
    }

    public function checkout_onepage_controller_success_action($observer) {
        foreach ($observer['order_ids'] as $orderId) {
            $this->sendAdminOrderNotification($orderId);
        }
    }

    /**
     * Order state protected setter.
     * By default allows to set any state. Can also update status to default or specified value
     * Сomplete and closed states are encapsulated intentionally, see the _checkState()
     *
     * @param string $state
     * @param string|bool $status
     * @param string $comment
     * @param bool $isCustomerNotified
     * @param $shouldProtectState
     * @return Mage_Sales_Model_Order
     */
    protected function _setState($state, $status = false, $comment = '', $isCustomerNotified = null, $shouldProtectState = false)
    {
        // dispatch an event before we attempt to do anything
        Mage::dispatchEvent('sales_order_status_change_before', array(
            'order' => $this, 
            'state' => $state, 
            'status' => $status, 
            'comment' => $comment, 
            'isCustomerNotified' => $isCustomerNotified, 
            'shouldProtectState' => $shouldProtectState
        ));

        // attempt to set the specified state
        if ($shouldProtectState) {
            if ($this->isStateProtected($state)) {
                Mage::throwException(
                    Mage::helper('sales')->__('The Order State "%s" must not be set manually.', $state)
                );
            }
        }
        $this->setData('state', $state);

        // add status history
        if ($status) {
            if ($status === true) {
                $status = $this->getConfig()->getStateDefaultStatus($state);
            }
            $this->setStatus($status);
            $history = $this->addStatusHistoryComment($comment, false); // no sense to set $status again
            $history->setIsCustomerNotified($isCustomerNotified); // for backwards compatibility
        }
        
        // dispatch an event after status has changed
        Mage::dispatchEvent('sales_order_status_change_after', array(
            'order' => $this, 
            'state' => $state, 
            'status' => $status, 
            'comment' => $comment, 
            'isCustomerNotified' => $isCustomerNotified, 
            'shouldProtectState' => $shouldProtectState
        ));
        
        return $this;
    }
    
}