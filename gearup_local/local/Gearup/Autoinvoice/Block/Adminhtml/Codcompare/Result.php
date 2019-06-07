<?php
class Gearup_Autoinvoice_Block_Adminhtml_Codcompare_Result extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPath() {
        return Mage::getBaseDir() . DS . 'media/dxbs/codcompare';
    }

    public function getCompareData($file) {
        return Mage::helper('gearup_sds')->getExcelData($file);
    }

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

    public function getOrderStatus($status) {
        $systemStatus = array_flip(Mage::getSingleton('sales/order_config')->getStatuses());
        $key = array_search($status, $systemStatus);
        return $key;
    }
}
