<?php
class Oye_SocialAuth_Block_Login extends Mage_Core_Block_Template
{
    protected $clientGoogle = null;
    protected $clientFacebook = null;
    protected $clientTwitter = null;

    protected $numEnabled = 0;
    protected $numDescShown = 0;
    protected $numButtShown = 0;

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

        Mage::register('oye_socialauth_button_text', $this->__('Login'));

        $this->setTemplate('oye/socialauth/login.phtml');
    }

    protected function _getColSet()
    {
        return 'col'.$this->numEnabled.'-set';
    }

    protected function _getDescCol()
    {
        return 'col-'.++$this->numDescShown;
    }

    protected function _getButtCol()
    {
        return 'col-'.++$this->numButtShown;
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
