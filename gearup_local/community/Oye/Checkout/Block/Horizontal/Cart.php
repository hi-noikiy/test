<?php

class Oye_Checkout_Block_Horizontal_Cart extends Mage_Checkout_Block_Cart
{

    protected function _construct()
    {
        $this->getCheckout()->setStepData('cart',  array(
            'label'   => Mage::helper('checkout')->__('Cart'),
            'allow'   => true
        ));
        parent::_construct();
    }

    public function isShow()
    {
        return true;
    }


}
