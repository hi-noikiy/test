<?php

/**
 * Class Gearup_Shippingffdx_Block_Order_Info rewrite
 */
class Gearup_Shippingffdx_Block_Order_Info extends Hatimeria_OrderManager_Block_Order_Info
{
    const STATUS_DELIVERED = 'Delivered to Customer';
    /**
     * Return Day of shipping
     *
     * @return array|string
     */
    public function getDayOfShipping()
    {
        $result = array();
        $order = $this->getOrder();
        $horder = Mage::getModel('hordermanager/order')->loadPeriodByOrderId($order->getId());

        if ($order->getStatus() == 'canceled' || $order->getStatus() == 'closed') {
            $result = 'closed';
        } else {
            $shipmentsCollection = $order->getShipmentsCollection();
            if ($horder->getEstimatedShipping() && $shipmentsCollection->getSize() == 0) {
                $sdsAll = Mage::helper('gearup_sds')->getSdsAll($order->getId());
                if ($sdsAll) {
                    $timestamp = Mage::app()->getLocale()->storeDate($this->getStore(), Varien_Date::toTimestamp($order->getCreatedAt()), true);
                    $estDate = Mage::helper('gearup_shippingffdx')->getEstDate($timestamp);
                    $date = Varien_Date::toTimestamp($estDate);
//                    $result = array(
//                        'label' => 'Estimate Shipping: ',
//                        'date' => $this->formatDate($estDate, 'long')
//                    );
                    $result = array(
                        'label' => 'Estimate Shipping: ',
                        'date' => date('j F Y', $date)
                    );
                } else {
                    $result = array(
                        'label' => 'Estimate Shipping: ',
                        'date' => $this->formatDate($horder->getEstimatedShipping(), 'long')
                    );
                }
            } elseif ($shipmentsCollection->getSize() > 0) {
                $shipment = Mage::getModel('sales/order_shipment')->load($order->getEntityId(), 'order_id');
                $result = array(
                    'label' => 'Shipped: ',
                    'date' => $this->formatDate($shipment->getCreatedAtStoreDate(), 'long')
                );
            } else {
                $collection = Mage::getResourceModel('ffdxshippingbox/history_collection')->getCompleteTrackingData($order->getEntityId());
                if ($collection->getSize() > 0) {
                    $lastActivity = $collection->getLastItem();
                    if (self::STATUS_DELIVERED == $lastActivity->getEvent()) {
                        $result = array(
                            'label' => 'Delivered: ',
                            'date' => $this->formatDate($lastActivity->getCreatedAtStoreDate(), 'long')
                        );
                    }
                } else {
                    $result = array(
                        'label' => 'Estimate Shipping: ',
                        'date' => 'Unknown'
                    );
                }
            }
        }

        return $result;
    }
}