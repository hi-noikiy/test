<?php
class Oye_SocialAuth_Model_Twitter_Userinfo
{
    protected $client = null;
    protected $userInfo = null;

    public function __construct() {
        if(!Mage::getSingleton('customer/session')->isLoggedIn())
            return;

        $this->client = Mage::getSingleton('oye_socialauth/twitter_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(($socialauthTid = $customer->getOyeSocialauthTid()) &&
                ($socialauthTtoken = $customer->getOyeSocialauthTtoken())) {
            $helper = Mage::helper('oye_socialauth/twitter');

            try{
                $this->client->setAccessToken($socialauthTtoken);
                
                $this->userInfo = $this->client->api('/account/verify_credentials.json', 'GET', array('skip_status' => true)); 

            }  catch (Oye_SocialAuth_TwitterOAuthException $e) {
                $helper->disconnect($customer);
                Mage::getSingleton('core/session')->addNotice($e->getMessage());                
            } catch(Exception $e) {
                $helper->disconnect($customer);
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }

        }
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }
}