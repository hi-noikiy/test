<?php

class Collinsharper_Beanstreaminterac_Model_Source_Paymentaction
{
    public function toOptionArray()
    {
        return array(
            array('value' => Collinsharper_Beanstreaminterac_Model_Api_Abstract::PAYMENT_TYPE_SALE, 'label' => Mage::helper('beanstreaminterac')->__('Sale')),
        );
    }
}