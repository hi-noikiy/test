<?php
class Oye_SocialAuth_Block_Twitter_Button extends Mage_Core_Block_Template
{
    protected $client = null;
    protected $userInfo = null;

    protected function _construct() {
        parent::_construct();

        $this->client = Mage::getSingleton('oye_socialauth/twitter_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('oye_socialauth_twitter_userinfo');

        if(!($redirect = Mage::getSingleton('customer/session')->getBeforeAuthUrl())) {
            $redirect = Mage::helper('core/url')->getCurrentUrl();      
        }

        // Redirect uri
        Mage::getSingleton('core/session')->setTwitterRedirect($redirect);

        $this->setTemplate('oye/socialauth/twitter/button.phtml');
    }

    protected function _getButtonUrl()
    {
        if(empty($this->userInfo)) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrl('socialauth/twitter/disconnect');
        }
    }

    protected function _getButtonText()
    {
        if(empty($this->userInfo)) {
            if(!($text = Mage::registry('oye_socialauth_button_text'))){
                $text = $this->__('Connect');
            }
        } else {
            $text = $this->__('Disconnect');
        }
        
        return $text;
    }

}
