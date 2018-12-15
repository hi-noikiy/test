<?php
class Ktpl_Customerordertracking_Helper_Sales_Guest extends Mage_Sales_Helper_Guest
{
	/**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @return bool|null
     */
    public function loadValidOrder()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/order/history'));
            return false;
        }

        $post = Mage::app()->getRequest()->getPost();
        $errors = false;

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');
        /** @var Mage_Core_Model_Cookie $cookieModel */
        $cookieModel = Mage::getSingleton('core/cookie');
        $errorMessage = 'Entered data is incorrect. Please try again.';

        if (empty($post) && !$cookieModel->get($this->_cookieName)) {
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/guest/form'));
            return false;
        } elseif (!empty($post) && isset($post['oar_order_id']) && isset($post['oar_type']))  {
            $type           = $post['oar_type'];
            $incrementId    = $post['oar_order_id'];
            $lastName       = $post['oar_billing_lastname'];
            $email          = $post['oar_email'];
            $zip            = $post['oar_zip'];

            if (empty($incrementId) || empty($lastName) || empty($type) || (!in_array($type, array('email', 'zip')))
                || ($type == 'email' && empty($email)) || ($type == 'zip' && empty($zip))) {
                $errors = true;
            }

            if (!$errors) {
                $order->loadByIncrementId($incrementId);
            }

            if ($order->getId()) {
                $billingAddress = $order->getBillingAddress();
                if ((strtolower($lastName) != strtolower($billingAddress->getLastname()))
                    || ($type == 'email'
                        && strtolower($email) != strtolower($order->getCustomerEmail()))
                    || ($type == 'zip'
                        && (strtolower($zip) != strtolower($billingAddress->getPostcode())))
                ) {
                    $errors = true;
                }
            } else {
                $errors = true;
            }

            // if ($errors === false && !is_null($order->getCustomerId())) {
            //     $errorMessage = 'Please log in to view your order details.';
            //     $errors = true;
            // }

            if (!$errors) {
                $toCookie = base64_encode($order->getProtectCode() . ':' . $incrementId);
                $cookieModel->set($this->_cookieName, $toCookie, $this->_lifeTime, '/');
            }
        } elseif (Mage::app()->getRequest()->getParam('order_id')){
            // customer should be also access all order by id.
            try{
                $orderId = Mage::app()->getRequest()->getParam('order_id');
                $order =  Mage::getModel('sales/order')->load($orderId);
            }catch(Exception $e){
                $errorMessage = $e->getMessage();
                $errors = true;
            }
        } elseif (Mage::app()->getRequest()->getParam('invoice_id')){
            try{
                $invoiceId = Mage::app()->getRequest()->getParam('invoice_id');
                $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
                $order = $invoice->getOrder();
            }catch(Exception $e){
                $errorMessage = $e->getMessage();
                $errors = true;
            }
        }elseif (Mage::app()->getRequest()->getParam('shipment_id')){
            try{
                $shipmentId = Mage::app()->getRequest()->getParam('shipment_id');
                $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                $order = $shipment->getOrder();
            }catch(Exception $e){
                $errorMessage = $e->getMessage();
                $errors = true;
            }
        }elseif (Mage::app()->getRequest()->getParam('creditmemo_id')){
            try{
                $creditmemoId = Mage::app()->getRequest()->getParam('creditmemo_id');
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
                $order = $creditmemo->getOrder();
            }catch(Exception $e){
                $errorMessage = $e->getMessage();
                $errors = true;
            }
        }

        if (!$errors && $order->getId()) {
            Mage::register('current_order', $order);
            return true;
        }

        Mage::getSingleton('core/session')->addError($this->__($errorMessage));
        Mage::app()->getResponse()->setRedirect(Mage::getUrl('sales/guest/form'));
        return false;
    }
}
		