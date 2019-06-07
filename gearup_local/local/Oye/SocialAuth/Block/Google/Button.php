<?php
class Oye_SocialAuth_Block_Google_Button extends Mage_Core_Block_Template
{
    protected $client = null;
    protected $oauth2 = null;
    protected $userInfo = null;

    protected function _construct() {
        parent::_construct();

        $this->client = Mage::getSingleton('oye_socialauth/google_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('oye_socialauth_google_userinfo');

        // CSRF protection
        if(!Mage::getSingleton('core/session')->getGoogleCsrf()) {


            $csrf = md5(uniqid(rand(), TRUE));

            Mage::getSingleton('core/session')->setGoogleCsrf($csrf);
        } else {
            $csrf = Mage::getSingleton('core/session')->getGoogleCsrf();
        }
        $this->client->setState($csrf);
        
        if(!($redirect = Mage::getSingleton('customer/session')->getBeforeAuthUrl())) {
            $redirect = Mage::helper('core/url')->getCurrentUrl();      
        }        
        
        // Redirect uri
        Mage::getSingleton('core/session')->setGoogleRedirect($redirect);        

        $this->setTemplate('oye/socialauth/google/button.phtml');
    }

    protected function _getButtonUrl()
    {
        if(empty($this->userInfo)) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrl('socialauth/google/disconnect');
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
