<?php
class Oye_Checkout_Block_Horizontal_Socialauth_Google_Button extends Oye_SocialAuth_Block_Google_Button
{

    protected function _construct()
    {
        parent::_construct();
        $redirect = Mage::getUrl('checkout/onepage/index', array('goto'=>'billing_shipping'));
        // Redirect uri
        Mage::getSingleton('core/session')->setGoogleRedirect($redirect);
    }

    protected function _getButtonText()
    {
        if (empty($this->userInfo)) {
            $text = $this->__('Connect');
        } else {
            $text = $this->__('Disconnect');
        }

        return $text;
    }

}
