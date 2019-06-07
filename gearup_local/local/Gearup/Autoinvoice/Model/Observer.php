<?php

class Gearup_Autoinvoice_Model_Observer
{
    public function implementOrderStatus($event)
    {
        $order = $event->getOrder();
        if ($this->_getPaymentMethod($order) == 'cashondelivery') {
            if ($order->canInvoice())
                $this->_processOrderStatus($order);
        }
        return $this;
    }

    private function _getPaymentMethod($order)
    {
        return $order->getPayment()->getMethodInstance()->getCode();
    }

    private function _processOrderStatus($order)
    {
        $invoice = $order->prepareInvoice();

        $invoice->register();
        Mage::getModel('core/resource_transaction')
           ->addObject($invoice)
           ->addObject($invoice->getOrder())
           ->save();
        $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);
        $invoice->save();
        $invoice->sendEmail(true, '');
        $this->_changeOrderStatus($order);
        return true;
    }

    private function _changeOrderStatus($order)
    {
        
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
        $order->save();
    }

    public function changeShipimentStatus(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        if ($this->_getPaymentMethod($order) == 'cashondelivery') {
            if ($order->hasInvoices()) {
                foreach ($order->getInvoiceCollection() as $inv) {
                    var_dump($inv->getData());
                }
            }
            die();
        }
        die();
    }

    public function changeInvoiceStatus(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (($this->_getPaymentMethod($order) == 'cashondelivery') || ($this->_getPaymentMethod($order) == 'bankpayment'))  {
            if ($order->getStatus() == Mage_Sales_Model_Order::STATE_CANCELED) {
                if ($order->hasInvoices()) {
                    foreach ($order->getInvoiceCollection() as $invoice) {
                        $invoice->setOrder($order);
                        $invoice->cancel();
                        $order->cancel();
                        $invoice->save();
                    }
                }
            }
        }
    }
}