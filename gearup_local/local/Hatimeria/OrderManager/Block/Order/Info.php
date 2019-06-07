<?php

/**
 * Class Hatimeria_OrderManager_Block_Order_Info
 */
class Hatimeria_OrderManager_Block_Order_Info extends Mage_Sales_Block_Order_Info
{
    const STATUS_DELIVERED = 1;

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Return Track number of service
     *
     * @internal param $customerId
     * @return array
     */
    public function getTrackNumberService()
    {
        $orderItems = Mage::getModel('sales/order_shipment_track')
            ->getCollection()
            ->addFieldToFilter('order_id', Mage::registry('current_order')->getId())
            ->load();

        $lastItem = $orderItems->getLastItem();

        $trackData = array(
            'track_number' => $lastItem->getTrackNumber(),
            'service' => $lastItem->getTitle()
        );

        return $trackData;
    }

    /**
     * Set data of shipping on $this
     *
     * @return $this
     */
    public function setShippingData()
    {
        $this->setData('day_of_shipping', $this->getDayOfShipping());
        $this->setData('tracking_numbers', $this->getTrackingNumbers());

        return $this;
    }

    /**
     * Return tracking numbers
     *
     * @return mixed
     */
    public function getTrackingNumbers()
    {
        $order = $this->getOrder();
        $trackingNumbers = Mage::getModel('ffdxshippingbox/tracking')->getCollection()
            ->addFieldToFilter('order_id', $order->getEntityId());

        return $trackingNumbers;
    }

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
                $result = array(
                    'label' => 'Estimate Shipping: ',
                    'date' => $this->formatDate($horder->getEstimatedShipping(), 'long')
                );
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
                    if (self::STATUS_DELIVERED == $lastActivity->getActivity()) {
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

    /**
     * Return links with updated link to printing invoice
     *
     * @return array
     */
//    public function getLinks()
//    {
//        $links = parent::getLinks();
//
//        if (!array_key_exists('invoice', $links)) {
//
//            return parent::getLinks();
//        }
//
//        $links['invoice']->setUrl($this->getPdfPrintUrl());
//
//        return $links;
//    }

    /**
     * Return url to printing the last invoice of order
     * by wkhtmltopdf library
     *
     * @return mixed
     */
//    public function getPdfPrintUrl()
//    {
//        $invoices = $this->getOrder()->getInvoiceCollection();
//
//        return $this->getChild('hwkhtmltopdf_invoice_items')->getPrintInvoiceUrl($invoices->getLastItem());
//    }
} 