<?php

class Oye_Checkout_Block_Horizontal_Onepage extends Mage_Checkout_Block_Onepage
{
    /**
     * Get checkout steps codes
     *
     * @return array
     */
    protected function _getStepCodes()
    {
        return array(
            'cart',
            'payment_shipping',
            'billing_shipping',
//            'login',
//            'billing',
//            'shipping',
//            'shipping_method',
//            'payment',
            'review');
    }


    public function getActiveStep()
    {
        return 'cart';
    }

    public function gotoBillingShippingStep()
    {
        return Mage::helper('oyecheckout')->getGotoStep() == 'billing_shipping';
    }

    public function gotoReviewStep()
    {
        return Mage::helper('oyecheckout')->getGotoStep() == 'review';
    }

}
