<?php

class Collinsharper_Beanstreaminterac_Model_Source_Standardaction
{
    public function toOptionArray()
    {
        return array(
            array('value' => Collinsharper_Beanstreaminterac_Model_Standard::PAYMENT_TYPE_AUTH, 'label' => Mage::helper('beanstreaminterac')->__('Authorization')),
            array('value' => Collinsharper_Beanstreaminterac_Model_Standard::PAYMENT_TYPE_SALE, 'label' => Mage::helper('beanstreaminterac')->__('Sale')),
        );
    }
}