<?php

/**
 * Class Hatimeria_WkHtmlToPdf_InvoiceController
 */
class Hatimeria_WkHtmlToPdf_InvoiceController extends Mage_Core_Controller_Front_Action
{
    /**
     * Print Invoice Action
     */
    public function printInvoiceAction()
    {
        $invoiceId = (int) $this->getRequest()->getParam('invoice_id');
        $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $order = $invoice->getOrder();
        } else {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
        }

        $invoicePdf = new Hatimeria_WkHtmlToPdf_Model_Pdf();
        $invoicePdf->createInvoicePdf($invoice, $order);

        $this->loadLayout();
        $this->renderLayout();
    }
}