<?php
class Gearup_Autoinvoice_Block_Adminhtml_Codcompare_Invoicelist extends Mage_Adminhtml_Block_Widget_Form
{

    public function getInvoicelist($from, $to) {
        return Mage::helper('gearup_autoinvoice')->getInvoicelist($from, $to);
    }

    public function getDateFormat($date) {
        return Mage::helper('core')->formatTime($date, 'medium', true);
    }

    public function getOrder($id) {
        $order = Mage::getModel('sales/order')->load($id);
        return $order;
    }

    public function getInvStatus($state) {
        switch ($state) {
            case Mage_Sales_Model_Order_Invoice::STATE_OPEN:
                $status = 'pending';
                break;
            case Mage_Sales_Model_Order_Invoice::STATE_PAID:
                $status = 'paid';
                break;
            case Mage_Sales_Model_Order_Invoice::STATE_CANCELED:
                $status = 'canceled';
                break;
            default:
                break;
        }

        return $status;
    }

    public function getPath() {
        return Mage::getBaseDir() . DS . 'media/dxbs/codcompare';
    }

    public function getCompareData($file) {
        return Mage::helper('gearup_sds')->getExcelData($file);
    }

    public function loadInvoice($id) {
        return Mage::getModel('sales/order_invoice')->load($id);
    }

    public function getOrderStatus($status) {
        $systemStatus = Mage::getSingleton('sales/order_config')->getStatuses();
        return $systemStatus[$status];
    }

    public function deliveredStatus($awb) {
        return Mage::helper('gearup_sds')->deliveredStatus($awb);
    }

    public function getOrderAwb($track) {
        $collection = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
        $collection->getSelect()->joinLeft(array("invoice" => 'sales_flat_invoice'), "main_table.order_id = invoice.order_id", array(
            "state" => "invoice.state",
            "increment_id" => "invoice.increment_id",
            "grand_total" => "invoice.grand_total",
            "invoice_id" => "invoice.entity_id"));
        $collection->addFieldToFilter('main_table.tracking_number', array('eq'=>$track));
        $collection->addFieldToFilter('main_table.checked', array('eq'=>1));
        $collection->addFieldToFilter('invoice.state', array('eq'=>1));
        $collection->getSelect()->group('main_table.order_id');

        if ($collection->getSize()) {
            return $collection->getFirstItem();
        } else {
            return '';
        }
    }

    public function getOrderAwbAll() {
        $collection = Mage::getModel('sales/order_invoice')->getCollection();
        $collection->getSelect()->joinLeft(array("track" => 'ffdxshippingbox_tracking'), "main_table.order_id = track.order_id", array("tracking_number" => "track.tracking_number", "checked" => "track.checked"));
        $collection->addFieldToFilter('main_table.state', array('eq'=>1));
        $collection->addFieldToFilter('track.checked', array('eq'=>1));
        $collection->getSelect()->group('main_table.order_id');

        return $collection;
    }

    public function getOrderTrack($order) {
        $collection = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
        $collection->addFieldToFilter('order_id', array('eq'=>$order->getId()));
        $collection->setOrder('tracking_id', 'DESC');

        if ($collection->getSize()) {
            return $collection->getFirstItem();
        } else {
            return '';
        }
    }

    public function getDeliveryDate($order) {
        return Mage::helper('gearup_sds')->deliveryDate($order);
    }
}
