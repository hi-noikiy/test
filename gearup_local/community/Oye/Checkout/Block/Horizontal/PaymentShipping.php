<?php

class Oye_Checkout_Block_Horizontal_PaymentShipping extends Mage_Checkout_Block_Onepage_Abstract
{

    protected function _construct()
    {
        $this->getCheckout()->setStepData('payment_shipping',  array(
            'label'   => Mage::helper('checkout')->__('Payment and Shipping'),
            'is_show'   => $this->isShow()
        ));
        parent::_construct();
    }

    public function isShow()
    {
        return true;
    }


}
