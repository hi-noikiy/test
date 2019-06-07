<?php

class Gearup_Autoinvoice_Adminhtml_Autoinvoice_CodcompareController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function filterinvoiceAction()
    {
        $from   = Mage::app()->getRequest()->getParam('invfrom');
        $to     = Mage::app()->getRequest()->getParam('invto');
        $this->_redirect('*/*/index', array('from'=>base64_encode($from), 'to'=>base64_encode($to)));
    }

    public function compareAction()
    {
        $data = $_FILES['comparefile'];
        $from = Mage::app()->getRequest()->getParam('codfrom');
        $to = Mage::app()->getRequest()->getParam('codto');
        $path = Mage::getBaseDir() . DS . 'media/dxbs/codcompare';
        if (!Mage::helper('gearup_sds')->checkFileType($data['type'])) {
            $this->_getSession()->addError(Mage::helper('adminhtml')->__('File upload not allow'));
            $this->_redirect('*/*/index');
            return false;
        }
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }
        if (file_exists($path.'/'.$data['name'])) {
            unlink($path.'/'.$data['name']);
        }
        if (!file_exists($path.'/'.$data['name'])) {
            move_uploaded_file($data['tmp_name'], $path.'/'.$data['name']);
            $this->_redirect('*/*/result', array('file'=>base64_encode($data['name']), 'from'=>$from, 'to'=>$to));
        }
    }

    public function resultAction()
    {
        $path = Mage::getBaseDir() . DS . 'media/dxbs/codcompare/';
        if (!Mage::app()->getRequest()->getParam('file') || !file_exists($path.base64_decode(Mage::app()->getRequest()->getParam('file')))) {
            $this->_redirect('*/*/index');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function changestatusAction()
    {
        try {
            $invoices = explode(',', $this->getRequest()->getParam('invoices'));
            $file = $this->getRequest()->getParam('file');
            $from = $this->getRequest()->getParam('from');
            $to = $this->getRequest()->getParam('to');
            $report = array();
            foreach ($invoices as $invoice) {
                if ($invoice) {
                    $invoiceModel = Mage::getModel('sales/order_invoice')->load($invoice);
                    $order = Mage::getModel('sales/order')->load($invoiceModel->getOrderId());
                    $invoiceModel->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
                    $invoiceModel->save();
                    Mage::helper('gearup_autoinvoice')->recordHistory($invoiceModel->getIncrementId(), 'Changed status from Pending to Paid');
                    $report[] = array(
                            'order'  => $order->getIncrementId(),
                            'amount' => $invoiceModel->getGrandTotal(),
                            'date'   => $order->getCreatedAt(),
                            'invoicenr'   => $invoiceModel->getIncrementId()
                        );
                }
            }
            Mage::getSingleton('core/session')->setInvoiceCReport($report);
            header('Content-type: application/json');
            echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/autoinvoice_codcompare/result', array('file'=>$file, 'from'=>$from, 'to'=>$to)), 'status' => 1));
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'invoiceerror.log');
        }

    }

    public function cancelinvoiceAction()
    {
        try {
            $invoices = explode(',', $this->getRequest()->getParam('invoices'));
            $file = $this->getRequest()->getParam('file');
            $from = $this->getRequest()->getParam('from');
            $to = $this->getRequest()->getParam('to');
            foreach ($invoices as $invoice) {
                if ($invoice) {
                    $invoiceModel = Mage::getModel('sales/order_invoice')->load($invoice);
                    $invoiceModel->setState(Mage_Sales_Model_Order_Invoice::STATE_CANCELED);
                    $invoiceModel->save();
                    Mage::helper('gearup_autoinvoice')->recordHistory($invoiceModel->getIncrementId(), 'Canceled invoice');
                }
            }

            header('Content-type: application/json');
            echo json_encode(array('url' => Mage::helper('adminhtml')->getUrl('*/autoinvoice_codcompare/result', array('file'=>$file, 'from'=>$from, 'to'=>$to)), 'status' => 1));
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'invoiceerror.log');
        }

    }

    public function exportInvoiceCAction() {
        Mage::getModel('gearup_autoinvoice/history')->downloadLastChange();
    }
}