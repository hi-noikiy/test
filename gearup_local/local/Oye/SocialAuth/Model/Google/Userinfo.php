<?php
class Oye_SocialAuth_Model_Google_Userinfo
{
    protected $client = null;
    protected $userInfo = null;

    public function __construct() {
        if(!Mage::getSingleton('customer/session')->isLoggedIn())
            return;

        $this->client = Mage::getSingleton('oye_socialauth/google_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(($socialauthGid = $customer->getOyeSocialauthGid()) &&
                ($socialauthGtoken = $customer->getOyeSocialauthGtoken())) {
            $helper = Mage::helper('oye_socialauth/google');

            try{
                $this->client->setAccessToken($socialauthGtoken);

                $this->userInfo = $this->client->api('/userinfo');

                /* The access token may have been updated automatically due to
                 * access type 'offline' */
                $customer->setOyeSocialauthGtoken($this->client->getAccessToken());
                $customer->save();

            } catch(Oye_SocialAuth_GoogleOAuthException $e) {
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