<?php

class Oye_Checkout_Block_Horizontal_BillingShipping extends Mage_Checkout_Block_Onepage_Abstract
{

    protected function _construct()
    {
        $this->getCheckout()->setStepData('billing_shipping',  array(
            'label'   => Mage::helper('checkout')->__('Address'),
            'is_show'   => $this->isShow()
        ));
        parent::_construct();
    }

    public function isShow()
    {
        return true;
    }


}
