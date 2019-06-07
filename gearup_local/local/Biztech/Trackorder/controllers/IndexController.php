<?php

class Biztech_Trackorder_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
            $this->loadLayout();
            $this->renderLayout();
        }else{
            $this->_redirect('customer/account');
        }
    }

    public function validate() {
        
    }

    public function initOrder() {
        if ($data = $this->getRequest()->getPost()) {
            $orderId = $data["order_id"];
            $email = $data["email"];
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $cEmail = $order->getCustomerEmail();
            if ($cEmail == trim($email)) {
                Mage::register('current_order', $order);
            } else {
                Mage::register('current_order', Mage::getModel("sales/order"));
            }
        }
    }

    public function trackAction() {
        //$orderId = $this->getRequest()->getPost()
        $post = $this->getRequest()->getPost();
        $error = false;
        if ($post) {
            try {
                if (!Zend_Validate::is(trim($post['order_id']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim($post['email']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }
                if ($error) {
                    throw new Exception();
                }
                $this->initOrder($post);
                $order = Mage::registry('current_order');
                if(!$order->getId()) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('contacts')->__('Order Not Found.Please try again later'));
                    $this->getResponse()->setBody($this->getLayout()->getMessagesBlock()->getGroupedHtml());
                    return;
                }
                $_POST['oar_type']   = 'email';
                $_POST['oar_order_id']    = $order->getIncrementId();
                $_POST['oar_billing_lastname']       = $order->getBillingAddress()->getLastname();
                $_POST['oar_email']          = $order->getCustomerEmail();
                $_POST['oar_zip']            = $order->getShippingAddress()->getPostcode();
                Mage::unregister('current_order');
                $isValidOrder = Mage::helper('trackorder/guest')->loadValidOrderNative();
                if ($order->getId() && $isValidOrder) {
                    $this->getResponse()->setBody($this->getLayout()->getMessagesBlock()->getGroupedHtml() . $this->_getGridHtml());
                    return;
                } else {
                    Mage::getSingleton('core/session')->addError(Mage::helper('trackorder')->__('Order Not Found.Please try again later'));
                    $this->getResponse()->setBody($this->getLayout()->getMessagesBlock()->getGroupedHtml());
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError(Mage::helper('trackorder')->__('Please Enter Order Detail.'));
                $this->getResponse()->setBody($this->getLayout()->getMessagesBlock()->getGroupedHtml());
                return;
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function viewAction() {


        $actionUrl = $_SERVER['REQUEST_URI'];
        $pathinfo = pathinfo($actionUrl);
        $trackid = $pathinfo['basename'];

            $orderdata = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToFilter('track_link', $trackid)
                    ->getData();

            $order = Mage::getModel('sales/order')->load($orderdata[0]['entity_id']);
            

            
            
            if ($order->getId()) {
                Mage::register('current_order', $order);
                 $this->loadLayout();
                 $this->renderLayout();
                return true;
               
            } else {
                    Mage::getSingleton('core/session')->addError($this->__('Order not found. Please try again later.'));
                    $this->_redirect('*/*/');
            }
    }
    
    protected function _getGridHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load("trackorder_index_track");
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    public function printInvoiceAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $orderObject = Mage::getModel('sales/order')->load($orderId);
        $invoiceCollection = $orderObject->getInvoiceCollection();
        foreach($invoiceCollection as $invoice) {
            $invoiceId =  $invoice->getId();
        }
        if (!empty($invoiceId)) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($invoice));
                return $this->_prepareDownloadResponse('invoice'.$orderObject->getIncrementId().
                    '.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_redirect('*/*/');
    }

}
