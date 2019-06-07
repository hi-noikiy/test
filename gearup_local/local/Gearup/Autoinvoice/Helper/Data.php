<?php

/**
 * Helper
 */

class Gearup_Autoinvoice_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NOT_MATCH = 'notmatch';
    const NOT_FOUND = 'notfound';

    public function getInvoicelist($from, $to) {
        $from = base64_decode($from);
        $to = base64_decode($to);
        $collection = Mage::getModel('sales/order_invoice')->getCollection();
        $collection->addFieldToFilter('main_table.created_at', array('gt'=>$from));
        $collection->addFieldToFilter('main_table.created_at', array('lt'=>$to));
        $collection->addFieldToFilter('main_table.state', array('eq'=>Mage_Sales_Model_Order_Invoice::STATE_OPEN));
        $collection->join(array('order' => 'order'), 'order.entity_id=order_id', array('order_created_at'=>'order.created_at'), null , 'left');
        $collection->setOrder('order_created_at', 'ASC');

        return $collection;
    }

    public function matchInvoice($order, $filedata) {
        $search = array_search($order->getIncrementId(), array_column($filedata, 'SenderRef'));
        if ($search !== false) {
            return $filedata[$search];
        } else {
            return self::NOT_FOUND;
        }
    }

    public function recordHistory($invoice, $action) {
        $history = Mage::getModel('gearup_autoinvoice/history');
        $admin = Mage::getSingleton('admin/session')->getUser();

        $history->setInvoiceId($invoice);
        $history->setActions($action);
        $history->setCreateDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        $history->setRecordBy($admin->getFirstname());
        $history->save();
    }

    public function matchAwb($order, $filedata) {
        if (!$order) {
            return '';
        }
        $search = array_search($order->getTrackingNumber(), array_column($filedata, 'Connote'));
        if ($search !== false) {
            return $filedata[$search];
        }
    }
}