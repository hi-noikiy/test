<?php

class Ktpl_Wholesaler_Block_Adminhtml_Sales_Order_Invoice_Totals extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals {

    /**
     * Initialize order totals array
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Invoice_Totals
     */
    protected function _initTotals() {
        parent::_initTotals();
        $source = $this->getSource();
//        $orderModel = $this->getOrder();
//        $amount = Mage::helper('wholesaler')->calculateTax($orderModel);
//        if ($amount) {
//            $this->addTotalBefore(new Varien_Object(array(
//                'code' => 'wholesaler_discount',
//                'value' => $amount,
//                'base_value' => $amount,
//                'label' => $this->helper('wholesaler')->getlabel(),
//                    ), array('tax')));
//        }
        if($source->getTierDiscount()){
            $this->addTotal(new Varien_Object(array(
                'code' => 'wholesaler_discount',
                'value' => $source->getTierDiscount(),
                'base_value' => $source->getTierDiscount(),
                'label' => $this->__('Wholesaler Discount'),
                    ), array('shipping', 'tax')));
        }
        if($source->getPaymentCharge()){
            $this->addTotal(new Varien_Object(array(
                'code' => 'payment_charge',
                'value' => $source->getPaymentCharge(),
                'base_value' => $source->getPaymentCharge(),
                'label' => $this->__('Credit Card Charge'),
                    ), array('shipping', 'tax')));
        }    
        return $this;
    }

}
