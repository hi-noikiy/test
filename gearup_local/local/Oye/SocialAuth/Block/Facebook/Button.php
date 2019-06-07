<?php

class Oye_SocialAuth_Block_Facebook_Button extends Mage_Core_Block_Template
{
    protected $client = null;
    protected $userInfo = null;
    protected $redirectUri = null;

    protected function _construct()
    {
        parent::_construct();

        $this->client = Mage::getSingleton('oye_socialauth/facebook_client');
        if (!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('oye_socialauth_facebook_userinfo');

        // CSRF protection
        if (!Mage::getSingleton('core/session')->getFacebookCsrf()) {
            $csrf = md5(uniqid(rand(), TRUE));
            Mage::getSingleton('core/session')->setFacebookCsrf($csrf);

        } else {
            $csrf = Mage::getSingleton('core/session')->getFacebookCsrf();
        }

        $this->client->setState($csrf);

        if (!($redirect = Mage::getSingleton('customer/session')->getBeforeAuthUrl())) {
            $redirect = Mage::helper('core/url')->getCurrentUrl();
        }

        // Redirect uri
        Mage::getSingleton('core/session')->setFacebookRedirect($redirect);

        $this->setTemplate('oye/socialauth/facebook/button.phtml');
    }

    protected function _getButtonUrl()
    {
        if (empty($this->userInfo)) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrl('socialauth/facebook/disconnect');
        }
    }

    protected function _getButtonText()
    {
        if (empty($this->userInfo)) {
            if (!($text = Mage::registry('oye_socialauth_button_text'))) {
                $text = $this->__('Connect');
            }
        } else {
            $text = $this->__('Disconnect');
        }

        return $text;
    }

}
