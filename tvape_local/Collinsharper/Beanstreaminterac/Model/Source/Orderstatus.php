<?php

class Collinsharper_Beanstreaminterac_Model_Source_Orderstatus
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'pending_payment', 'label' => 'Pending Beanstream Payments'),
        );
    }
}