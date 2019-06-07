<?php

class FFDX_ShippingBox_Block_History extends Mage_Core_Block_Template
{
    /**
     * test variables
     */
    const ORDER_IS_UNCHECKED = '100';

    protected $tracking;

    /**
     * @return Mage_Core_Block_Abstract|void
     */
    protected function _prepareLayout()
    {

    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        $customerId = $this->getCustomer()->getId();

        return $customerId;
    }

    /**
     * @internal param $customerId
     * @return mixed
     */
    public function getOrderId()
    {
        $customerOrders = Mage::getModel('sales/order_shipment')->getCollection()
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->load();

        $order = $customerOrders->getLastItem();
        $orderId = null;
        if ($order->getId()) {
            $orderId = $order->getOrderId();
        }

        return $orderId;
    }

    public function getHistory($orderId)
    {
        return Mage::getResourceModel('ffdxshippingbox/history_collection')->getCompleteTrackingData($orderId);
    }

    /**
     * @return bool
     */
    public function getTracking()
    {
        if (!isset($this->tracking)) {
            $this->tracking = false;
            $trackingCollection = Mage::getModel('ffdxshippingbox/tracking')
                ->getCollection()
                ->addFieldToFilter('order_id', Mage::registry('current_order')->getId())
                ->load();

            $lastTracking = $trackingCollection->getLastItem();
            if (!$lastTracking->isObjectNew()) {
                $this->tracking = $lastTracking;
            }
        }

        return $this->tracking;
    }

    /**
     * @internal param $customerId
     * @return mixed
     */
    public function getShipmentActivityOld()
    {
        $result = '';
        $tracking = $this->getTracking();
        if ($tracking) {
            $history = $this->getHistory($tracking->getOrderId());
            $shippingHistory = new Varien_Data_Collection();
            $history = array_reverse($history->getItems());

            if (count($history) > 0) {

                foreach ($history as $event) {
                    $activityCode = $this->getEventCode($event->getActivity());
                    $event->setActivity($activityCode);
                    $shippingHistory->addItem($event);
                }

                $result = $shippingHistory;

            } else {
                $result = $this->getEventcode(self::ORDER_IS_UNCHECKED);
            }
        }

        return $result;
    }
    
    

    /**
     * @internal param $customerId
     * @return mixed
     */
    public function getShipmentActivity()
    {
        $result = '';
        $tracking = $this->getTracking();
        if ($tracking) {
            $history = $this->getHistory($tracking->getOrderId());
            $shippingHistory = new Varien_Data_Collection();
            $history = array_reverse($history->getItems());

            if (count($history) > 0) {

                foreach ($history as $event) {
                    $activityCode = $this->getEventCode($event->getEvent());
                    $event->setActivity($activityCode);
                    $shippingHistory->addItem($event);
                }

                return $shippingHistory;

            } else {
                $result = $this->getEventcode(self::ORDER_IS_UNCHECKED);
            }
        }

        return $result;
    }
    
    

    /**
     * @return mixed
     */
    public function getDescription()
    {
        $description = $this->getEventCode(self::ORDER_IS_UNCHECKED);

        return $description;
    }


    /**
     * @param $activityNumber
     * @return mixed
     */
    public function getEventCodeOld($activityNumber)
    {
        $event = 'Waiting for check';
        if ($activityNumber) {
            $source = Mage::getModel('ffdxshippingbox/source_event_code');
            $eventCodes = $source->getMap();

            $event = $eventCodes[$activityNumber];
        }

        return $event;
    }

    /**
     * @param $activityNumber
     * @return mixed
     */
    public function getEventCode($activityNumber)
    {
        $event = 'Waiting for check';
        if ($activityNumber) {
            $source = Mage::getModel('ffdxshippingbox/source_event_code');
            $eventCodes = $source->getNewMap();
            $event = $eventCodes[$activityNumber];
        }

        return $event;
    }

    /**
     * @internal param $customerId
     * @return string
     */
    public function getWeight()
    {
        $orderItems = Mage::getModel('sales/order_shipment')
            ->getCollection()
            ->addFieldToFilter('order_id', $this->getOrderId())
            ->load();

        $lastItem = $orderItems->getLastItem();
        $weight = $lastItem->getTotalWeight();

        if (null == $weight) {
            $weight = '0.0';
            return $weight;
        } else {
            return $weight;
        }
    }

    public function checkIsShipmentSet()
    {
        $result = true;
        $shipment = Mage::getModel('sales/order_shipment')
            ->getCollection()
            ->addFieldToFilter('order_id', Mage::registry('current_order')->getId())
            ->load();

        if (!$shipment->getData()) {
            $result = null;
        }

        return $result;
    }
} 