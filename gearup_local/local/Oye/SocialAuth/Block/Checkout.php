<?php
class Oye_SocialAuth_Block_Checkout extends Mage_Core_Block_Template
{
    protected $clientGoogle = null;
    protected $clientFacebook = null;
    protected $clientTwitter = null;
    
    protected $numEnabled = 0;
    protected $numShown = 0;    

    protected function _construct() {
        parent::_construct();

        $this->clientGoogle = Mage::getSingleton('oye_socialauth/google_client');
        $this->clientFacebook = Mage::getSingleton('oye_socialauth/facebook_client');
        $this->clientTwitter = Mage::getSingleton('oye_socialauth/twitter_client');

        if( !$this->_googleEnabled() &&
            !$this->_facebookEnabled() &&
            !$this->_twitterEnabled())
            return;

        if($this->_googleEnabled()) {
            $this->numEnabled++;
        }

        if($this->_facebookEnabled()) {
            $this->numEnabled++;
        }

        if($this->_twitterEnabled()) {
            $this->numEnabled++;
        }
        
	if(Mage::registry('oye_socialauth_button_text')) {
		Mage::unregister('oye_socialauth_button_text');
	}
        Mage::register('oye_socialauth_button_text', $this->__('Continue'));

        $this->setTemplate('oye/socialauth/checkout.phtml');
    }
    
    protected function _getColSet()
    {
        return 'col'.$this->numEnabled.'-set';
    }

    protected function _getCol()
    {
        return 'col-'.++$this->numShown;
    }    

    protected function _googleEnabled()
    {
        return $this->clientGoogle->isEnabled();
    }

    protected function _facebookEnabled()
    {
        return $this->clientFacebook->isEnabled();
    }

    protected function _twitterEnabled()
    {
        return $this->clientTwitter->isEnabled();
    }

}
