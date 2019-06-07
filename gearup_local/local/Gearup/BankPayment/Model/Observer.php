<?php

class Gearup_BankPayment_Model_Observer {


    public function beforeSendOrder($observer){
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $order = $observer->getEvent()->getObject();
        $configPath = $update ? 'order_comment' : 'order';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $order->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PdfCustomiser
            $pdf = Mage::getModel('emailattachments/order_pdf_order')->getPdf(array($order));
            $mailTemplate = Mage::helper('emailattachments')->addAttachment(
                $pdf, $mailTemplate, Mage::helper('sales')->__('Order') . "_" . $order->getIncrementId()
            );
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $order->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($order->getStoreId(), $mailTemplate);
        }

        $fileAttachment = Mage::getStoreConfig('sales_email/' . $configPath . '/attachfile', $order->getStoreId());
        if ($fileAttachment) {
            $mailTemplate = Mage::helper('emailattachments')->addFileAttachment($fileAttachment, $mailTemplate);
        }

        $paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode();
        if ($paymentMethodCode == 'bankpayment'){
            $fileAttachment = Mage::getStoreConfig('sales_email/' . $configPath . '/bankpaymentatt', $order->getStoreId());
            if ($fileAttachment) {
                $mailTemplate = Mage::helper('emailattachments')->addFileBankAttachment($fileAttachment, $mailTemplate);
            }
        }
    }

    public function autoInvoice($observer) {
        // loading placed order using observer.

        $order = $observer->getEvent()->getOrder();
        $orders = Mage::getModel('sales/order_invoice')->getCollection()->addAttributeToFilter('order_id', array('eq' => $order->getId()));
        $orders->getSelect()->limit(1);

        if ((int) $orders->count() !== 0) {
            return $this;
        }
        if ($order->getPayment()->getMethod() !== 'bankpayment')
            return $this;

        try {
            // checking the order can invoice or not.

            if (!$order->canInvoice()) {
                $order->addStatusHistoryComment('AutoInvoice: Order cannot be invoiced.', false);
                $order->save();
            } else {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                // below capture method depends on your payment method.
                // here I used CAPTURE_OFFLINE method.

                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::NOT_CAPTURE);
                //$order->getPayment()->setForcedState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);
                $invoice->register();
                //$invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);                

                $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                $transactionSave->save();

                // Now its setting the status to processing. 
                // for virtual products this might not required. 
                // please test only with invoice generate code.
                //$invoice->getOrder()->setIsInProcess(true);
                $invoice->getOrder()->setIsInProcess(false);
                $order->addStatusHistoryComment(Mage::helper('core')->__('Auto Invoice generated.'), Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)->setIsCustomerNotified(true);
                $invoice->sendEmail(true, '');               // $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
                $order->save();
            }
        } catch (Exception $e) {
            $order->addStatusHistoryComment('AutoInvoice: Exception occurred during autoInvoice action. Exception message: ' . $e->getMessage(), false);
            $order->save();
        }
        return $this;
    }

    public function beforeSendInvoice($observer) {

        $fileAttachment = trim(Mage::getStoreConfig('payment/bankpayment/pdf_upload_file'));
        
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $invoice = $observer->getEvent()->getObject();
        if (!$invoice){
            return;
        }

        if ($invoice->getOrder()->getPayment()->getMethod() !== 'bankpayment')
            return;


        //$fileAttachment = 'test.pdf';
        
        if ($fileAttachment) {
            $mailTemplate = Mage::helper('emailattachments')->addFileAttachment($fileAttachment, $mailTemplate);
        }
    }

}
