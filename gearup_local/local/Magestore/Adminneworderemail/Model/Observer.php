<?php

class Magestore_Adminneworderemail_Model_Observer {

    const EMAIL_NEW_ORDER_TEMPLATE = 'sales_email/order/admin_email_template';
    const EMAIL_CANCEL_ORDER_TEMPLATE = 'sales_email/order/admin_email_cancel_template';

    public function onepageCheckoutSuccess($observer) 
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $status = $order->getStatus();
        $allowedStatuses = $this->getAllowedStatuses();

        //file_put_contents('/var/www/gearup/vardump', "ACTION FROM CHECKOUT\n", FILE_APPEND);
        //file_put_contents('/var/www/gearup/vardump', "Status of order: ".$status."\n", FILE_APPEND);
        //file_put_contents('/var/www/gearup/vardump', print_r($allowedStatuses, true)."\n", FILE_APPEND);

        if (in_array($status, $allowedStatuses)) {
            $this->sendAdminOrderNotification($orderId, $this->getStoreId(), self::EMAIL_NEW_ORDER_TEMPLATE);
        }
    }

    public function submitAdminOrder($observer) 
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();

        $status = $order->getStatus();
        $allowedStatuses = $this->getAllowedStatuses();

        //file_put_contents('/var/www/gearup/vardump', "ACTION FROM ADMIN PANEL\n", FILE_APPEND);
        //file_put_contents('/var/www/gearup/vardump', "Status of order: " . $status."\n", FILE_APPEND);
        //file_put_contents('/var/www/gearup/vardump', print_r($allowedStatuses, true)."\n", FILE_APPEND);

        if (in_array($status, $allowedStatuses)) {
            $this->sendAdminOrderNotification($order->getId(), $quote->getStoreId(), self::EMAIL_NEW_ORDER_TEMPLATE);
        }
    }
    
    public function cancelAdminOrder($observer)
    {
        $order = $observer->getOrder();
        if (Mage::app()->getRequest()->getParam('silent', false)) {
            // Do not send cancel email when user clicked "Silent Cancel"
            return false;
        }
        $this->sendAdminOrderNotification($order->getId(), $order->getStoreId(), self::EMAIL_CANCEL_ORDER_TEMPLATE);
    }
    

    public function sendAdminOrderNotification($orderId, $storeId, $templatePath) 
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        //file_put_contents('/var/www/gearup/vardump', "Try to send notification..\n", FILE_APPEND);

        $mailTemplate = Mage::getModel('core/email_template');
        $template = Mage::getStoreConfig($templatePath, $storeId);
        $adminEmail = '';
        $adminName = '';
        $_order = Mage::getModel('sales/order')->load($orderId);

        $paymentBlock = Mage::helper('payment')->getInfoBlock($_order->getPayment())->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($_order->getStore()->getId());

        $adminEmailString = Mage::getStoreConfig('sales_email/order/admin_email_notify');
        $adminEmailArray = explode(',', $adminEmailString);

        foreach ($adminEmailArray as $adminEmail) {
            //file_put_contents('/var/www/gearup/vardump', "Sending to: {$adminEmail}..\n", FILE_APPEND);
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                ->sendTransactional($template, Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId), $adminEmail, $adminName, array(
                    'order' => $_order,
                    'payment_html' => $paymentBlock->toHtml(),
                )
            );
        }

        $translate->setTranslateInline(true);
    }
    
    protected function getAllowedStatuses()
    {
        return explode(",", Mage::getStoreConfig('sales_email/order/admin_email_notify_status'));
    }

    public function getStoreId() 
    {
        return Mage::app()->getStore()->getId();
    }

}
