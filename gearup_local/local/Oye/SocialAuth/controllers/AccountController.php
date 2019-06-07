<?php
class Oye_SocialAuth_AccountController extends Mage_Core_Controller_Front_Action
{
    
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }
        
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }    

    public function googleAction()
    {        
        $userInfo = Mage::getSingleton('oye_socialauth/google_userinfo')
                ->getUserInfo();
        
        Mage::register('oye_socialauth_google_userinfo', $userInfo);
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function facebookAction()
    {        
        $userInfo = Mage::getSingleton('oye_socialauth/facebook_userinfo')
            ->getUserInfo();
        
        Mage::register('oye_socialauth_facebook_userinfo', $userInfo);
        
        $this->loadLayout();
        $this->renderLayout();
    }    
    
    public function twitterAction()
    {        
        // Cache user info inside customer session due to Twitter window frame rate limits
        if(!($userInfo = Mage::getSingleton('customer/session')
                ->getOyeSocialauthTwitterUserinfo())) {
            $userInfo = Mage::getSingleton('oye_socialauth/twitter_userinfo')
                ->getUserInfo();
            
            Mage::getSingleton('customer/session')->setOyeSocialauthTwitterUserinfo($userInfo);
        }
        
        Mage::register('oye_socialauth_twitter_userinfo', $userInfo);
        
        $this->loadLayout();
        $this->renderLayout();
    }    
    
}
