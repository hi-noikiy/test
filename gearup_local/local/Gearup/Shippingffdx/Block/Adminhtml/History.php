<?php

class Gearup_Shippingffdx_Block_Adminhtml_History extends FFDX_ShippingBox_Block_Adminhtml_History
{

    public function __construct()
    {
        parent::__construct();
        $trackingId = Mage::app()->getRequest()->getParam('tracking_id');
        $track = Mage::getModel('ffdxshippingbox/tracking')->load($trackingId);
        $ref = Mage::helper('ffdxshippingbox')->getTrackingRef($track->getTrackingNumber());
        $postapluslink = 'http://www.postaplus.com/Customer/ShipmentDetails.aspx?sno=';

        $orderId = $track->getOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $incrementNumber = $order->getIncrementId();
        $orderView = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=> $orderId));

        if ($ref) {
            if ($ref->getRefTrackingNumber()) {
                $this->_headerText = Mage::helper('ffdxshippingbox')->__('FFDX ShippingBox History Of Tracking: ' . '<a target="_blank" href="' . $postapluslink . $track->getTrackingNumber() . '">' . $track->getTrackingNumber()) . '</a> '
                        . Mage::helper('ffdxshippingbox')->__('Reference: ' . '<a target="_blank" href="http://www.aramex.com/express/track.aspx">' . $ref->getRefTrackingNumber()) . '</a>'.' | '. Mage::helper('ffdxshippingbox')->__('Order Number: ' .'<a target="_blank" href="' . $orderView) . '">' . $incrementNumber . '</a>';
            } else {
                $this->_headerText =  Mage::helper('ffdxshippingbox')->__('FFDX ShippingBox History Of Tracking: ' . '<a target="_blank" href="' . $postapluslink . $track->getTrackingNumber() . '">' . $track->getTrackingNumber()) . '</a>'.' | '. Mage::helper('ffdxshippingbox')->__('Order Number: ' .'<a target="_blank" href="' . $orderView) . '">' . $incrementNumber . '</a>';
            }
        } else {
            $this->_headerText =  Mage::helper('ffdxshippingbox')->__('FFDX ShippingBox History Of Tracking: ' . '<a target="_blank" href="' . $postapluslink . $track->getTrackingNumber() . '">' . $track->getTrackingNumber()) . '</a>' .' | '. Mage::helper('ffdxshippingbox')->__('Order Number: ' .'<a target="_blank" href="' . $orderView) . '">' . $incrementNumber . '</a>';
        }

        if (!$track->getChecked()) {
            $this->addButton('updated_track', array(
                'label'     => Mage::helper('ffdxshippingbox')->__('Change status to Yes'),
                'onclick'   => 'if(confirm(\'Are you sure you want to change status?\')) setLocation(\'' . $this->getUrl('adminhtml/shippingffdx/changestatus', array('tracking_id'=>$trackingId)) .'\')',
                'class'     => 'refresh'
            ));
        }
    }
}