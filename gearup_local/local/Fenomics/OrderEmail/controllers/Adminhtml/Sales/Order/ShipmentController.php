<?php

require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';

class Fenomics_OrderEmail_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    /**
     * Print label for one specific shipment
     *
     */
    public function printLabelAction()
    {
        try {
            $shipment = $this->_initShipment();
            $labelContent = $shipment->getShippingLabel();
            if ($labelContent) {
                $pdfContent = null;
                if (stripos($labelContent, '%PDF-') !== false) {
                    $pdfContent = $labelContent;
                } else {
                    $pdf = new Zend_Pdf();
                    $page = $this->_createPdfPageFromImageString($labelContent);
                    if (!$page) {
                        $this->_getSession()->addError(Mage::helper('sales')->__('File extension not known or unsupported type in the following shipment: %s', $shipment->getIncrementId()));
                    }
                    $pdf->pages[] = $page;
                    $pdfContent = $pdf->render();
                }

                return $this->_prepareDownloadResponse(
                    'ShippingLabel(' . $shipment->getOrder()->getIncrementId() . ').pdf',
                    $pdfContent,
                    'application/pdf'
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()
                ->addError(Mage::helper('sales')->__('An error occurred while creating shipping label.'));
       }
       $this->_redirect('*/sales_order_shipment/view', array(
           'shipment_id' => $this->getRequest()->getParam('shipment_id')
       ));
    }
}