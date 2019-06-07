<?php
class Gearup_Shippingffdx_Block_Adminhtml_Reportcompare_Shippinglist extends Mage_Adminhtml_Block_Widget_Grid
{
    public function getShippinglist($from, $to, $desti) {
        return Mage::helper('gearup_shippingffdx')->getShippinglist($from, $to, $desti);
    }

    public function getDateFormat($date) {
        return Mage::helper('core')->formatTime($date, 'medium', true);
    }

    public function getOrder($id) {
        $order = Mage::getModel('sales/order')->load($id);
        return $order;
    }

    public function getReference($track) {
        $ref = Mage::helper('ffdxshippingbox')->getTrackingRef($track);
        return $ref ? $ref->getRefTrackingNumber() : '';
    }

    public function getCompareData($file) {
        $type = base64_decode(Mage::app()->getRequest()->getParam('desti'));
        return Mage::helper('gearup_sds')->getExcelShipingData($file, $type);
    }

    public function getPath() {
        return Mage::getBaseDir() . DS . 'media/dxbs/shippingcompare';
    }

    public function getPayment($order) {
        $payment = $order->getPayment();
        if ($payment->getMethodInstance()->getCode() == 'cashondelivery') {
            return '10' . ' ' . Mage::app()->getStore()->getCurrentCurrencyCode();
        } else {
            return '';
        }
    }
}
