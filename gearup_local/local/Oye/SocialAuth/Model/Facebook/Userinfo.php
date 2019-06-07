<?php
class Oye_SocialAuth_Model_Facebook_Userinfo
{
    protected $client = null;
    protected $userInfo = null;

    public function __construct() {
        if(!Mage::getSingleton('customer/session')->isLoggedIn())
            return;

        $this->client = Mage::getSingleton('oye_socialauth/facebook_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(($socialauthFid = $customer->getOyeSocialauthFid()) &&
                ($socialauthFtoken = $customer->getOyeSocialauthFtoken())) {
            $helper = Mage::helper('oye_socialauth/facebook');

            try{
                $this->client->setAccessToken($socialauthFtoken);
                $this->userInfo = $this->client->api(
                    '/me',
                    'GET',
                    array(
                        'fields' =>
                        'id,name,first_name,last_name,link,birthday,gender,email,picture.type(large)'
                    )
                );

            } catch(Oye_SocialAuth_FacebookOAuthException $e) {
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