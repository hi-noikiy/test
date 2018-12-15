<?php
class Thinkhigh_VPCpaymentgateway_Model_Source{
	public function toOptionArray(){
    return array(
      array('value' => 'ssl', 'label' => Mage::helper('vpcpaymentgateway')->__('Auth-Purchase with 3DS Authentication')),
      array('value' => 'threeDSecure', 'label' => Mage::helper('vpcpaymentgateway')->__('3DS Authentication Only')),
    );
  }
}
?>