<?php
class Thinkhigh_VPCpaymentgateway_Model_Source_Payment_Vpc{
	public function toOptionArray(){
    return array(
      array('value' => 'Mastercard', 'label' => Mage::helper('vpcpaymentgateway')->__('Mastercard')),
      array('value' => 'Visa', 'label' => Mage::helper('vpcpaymentgateway')->__('Visa')),
      array('value' => 'Amex', 'label' => Mage::helper('vpcpaymentgateway')->__('American Express')),
      array('value' => 'AmexPurchaseCard', 'label' => Mage::helper('vpcpaymentgateway')->__('Amex Corporate Purchase Card')),
    );
  }
}
?>