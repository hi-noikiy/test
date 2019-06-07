<?php

class Gearup_Shippingffdx_Model_Tracktype extends Mage_Core_Model_Abstract {

    const STATE_COMPLETE_DELIVERED        = 'complete_delivered';
    const STATE_PROCESS_DELIVERED         = 'processing_delivered';
    const STATE_PROCESS_SHIPPED           = 'processing_shipped';
    const STATE_NEW_SHIPPED               = 'new_shipped';

    public function _construct(){
        parent::_construct();
        $this->_init("gearup_shippingffdx/tracktype");
    }

    public function saveShippingType($trackingNr, $type)
    {
        $search = $this->getShippingType($trackingNr);
        if (!$search->getId()) {
            $model = Mage::getModel('gearup_shippingffdx/tracktype');
            $model->setTrackingNumber($trackingNr);
            $model->setType($type);
            $model->save();
        }

    }

    public function getShippingType($trackingNr)
    {
        $model = Mage::getModel('gearup_shippingffdx/tracktype')->getCollection();
        $model->addFieldToFilter('tracking_number', array('eq'=>$trackingNr));
        $search = $model->getFirstItem();
        return $search;
    }


    public function changeOrderStatus($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        if ($order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE || $order->getStatus() == self::STATE_PROCESS_SHIPPED || $order->getStatus() == self::STATE_NEW_SHIPPED) {
            if ($this->_getPaymentMethod($order) == 'cashondelivery') {
                if ($order->hasInvoices()) {
                    foreach ($order->getInvoiceCollection() as $invoice) {
                        if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN) {
                            $order->setStatus(self::STATE_PROCESS_DELIVERED, true);
                        } else {
                            $order->setStatus(self::STATE_COMPLETE_DELIVERED, true);
                        }
                    }
                }
            } else {
                $order->setStatus(self::STATE_COMPLETE_DELIVERED, true);
            }
            $order->save();
        }
    }

    public function _getPaymentMethod($order)
    {
        return $order->getPayment()->getMethodInstance()->getCode();
    }

    public function updateRefTrack($track)
    {
        $model = Mage::getModel('gearup_shippingffdx/tracktype')->getCollection();
        $model->addFieldToFilter('tracking_number', array('eq'=>$track['ReferenceNumber']));
        $search = $model->getFirstItem();

        if ($search->getRefTrackingNumber()) {
            $search->setRefTrackingNumber($track['AlternateReference']);
            $search->save();
        } else {
            $search->setRefTrackingNumber($track['AlternateReference']);
            $search->save();
            //$this->sendSms($track['AlternateReference']);
            $this->sendEmail($track);
        }
    }

    public function sendEmail($trackRef)
    {
        try {
            $track = Mage::getModel('ffdxshippingbox/tracking')->load($trackRef['ReferenceNumber'], 'tracking_number');
            $order = Mage::getModel('sales/order')->load($track->getOrderId());
            $shipment = $order->getShipmentsCollection()->getFirstItem();
            $shipment = Mage::getModel('sales/order_shipment')->load($shipment->getId());
            if ($shipment) {
                $shipment->sendRefEmail(true)
                    ->setEmailSent(true)
                    ->save();
            }
        } catch (Exception $exc) {
            Mage::log($exc->getMessage(), null, 'mailffdxref.log');
        }
    }

    public function deletePreviousH($trackId)
    {
        $histories = Mage::getModel('ffdxshippingbox/history')->getCollection();
        $histories->addFieldToFilter('tracking_id', array('eq'=>$trackId));
        if ($histories->getSize()) {
            foreach ($histories as $history) {
                $history->delete();
            }
        }
    }
}
