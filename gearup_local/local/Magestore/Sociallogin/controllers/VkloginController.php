<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */
class Magestore_Sociallogin_VkloginController extends Mage_Core_Controller_Front_Action
{

    /**
     * @return bool|mixed|void
     */
    public function loginAction() {
        if (!$this->getAuthorizedToken()) {
            $token = $this->getAuthorization();
        } else {
            $token = $this->getAuthorizedToken();
        }

        return $token;
    }

    /**
     * @param $vkId
     * @return null
     */
    public function getCustomerId($vkId) {
        $user = Mage::getModel('sociallogin/vklogin')->getCollection()
            ->addFieldToFilter('vk_id', $vkId)
            ->getFirstItem();
        if ($user)
            return $user->getCustomerId();
        else
            return NULL;
    }

    /**
     * @param $vkId
     * @param $customerId
     */
    public function setAuthorCustomer($vkId, $customerId) {
        $mod = Mage::getModel('sociallogin/vklogin');
        $mod->setData('vk_id', $vkId);
        $mod->setData('customer_id', $customerId);
        $mod->save();
        return;
    }

    /**
     *
     */
    public function userAction() {
        $vklogin = Mage::getModel('sociallogin/vklogin')->getVk();
        // $oauth2 = new Google_Oauth2Service($gologin);
        $code = $this->getRequest()->getParam('code');
        if (!$code) {
            Mage::getSingleton('core/session')->addError($this->__('Login failed as you have not granted access.'));
            $this->getResponse()->setBody("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . Mage::app()->getStore()->getBaseUrl() . "\"} window.close();</script>");
        }
        $redirectUrl = Mage::app()->getStore()->getBaseUrl() . 'sociallogin/vklogin/user';
        $accessToken = $vklogin->getAccessToken($code, $redirectUrl);
        $userId = $accessToken['user_id'];
        $users = $vklogin->api('getProfiles', array(
            'uids' => $userId,
            'fields' => 'first_name, last_name'));

        foreach ($users['response'] as $userVk) {
            $user = $userVk;
            break;
        }
        $vkId = $user['uid'];

        $customerId = $this->getCustomerId($vkId);
        //get website_id and sote_id of each stores
        //$store_id = Mage::app()->getStore()->getStoreId();//add
        //$website_id = Mage::app()->getStore()->getWebsiteId();//add

        //$customer = Mage::helper('sociallogin')->getCustomerByEmail($user['email'], $website_id);//add edition
        if ($customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            // fix confirmation
            if ($customer->getConfirmation()) {
                try {
                    $customer->setConfirmation(null);
                    $customer->save();
                } catch (Exception $e) {
                }
            }
            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
            $this->getResponse()->setBody("<script type=\"text/javascript\">if(navigator.userAgent.match('CriOS')){window.location.href=\"" . $this->_loginPostRedirect() . "\";}else{try{window.opener.location.href=\"" . $this->_loginPostRedirect() . "\";}catch(e){window.opener.location.reload(true);} window.close();}</script>");
        } else {
            $user['firstname'] = $user['first_name'];
            $user['lastname'] = $user['last_name'];
            $name = $user['firstname'] . $user['lastname'];
            $email = $name . '@vk.com';
            $user['email'] = $email;
            //get website_id and sote_id of each stores
            $store_id = Mage::app()->getStore()->getStoreId();
            $website_id = Mage::app()->getStore()->getWebsiteId();
            $customer = Mage::helper('sociallogin')->getCustomerByEmail($user['email'], $website_id);//add edtition
            if (!$customer || !$customer->getId()) {
                //Login multisite
                $customer = Mage::helper('sociallogin')->createCustomerMultiWebsite($user, $website_id, $store_id);
                if (Mage::getStoreConfig(('sociallogin/general/send_newemail'), Mage::app()->getStore()->getId())) $customer->sendNewAccountEmail('registered', '', Mage::app()->getStore()->getId());
            }
            // fix confirmation
            if ($customer->getConfirmation()) {
                try {
                    $customer->setConfirmation(null);
                    $customer->save();
                } catch (Exception $e) {
                }
            }
            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
            $this->setAuthorCustomer($vkId, $customer->getId());
            Mage::getSingleton('core/session')->setCustomerIdSocialLogin($vkId);
            if (Mage::getStoreConfig('sociallogin/vklogin/is_send_password_to_customer')) {
                $customer->sendPasswordReminderEmail();
            }
            $nextUrl = Mage::helper('sociallogin')->getEditUrl();
            Mage::getSingleton('core/session')->addNotice('Please enter your contact detail.');
            $this->getResponse()->setBody("<script>window.close();window.opener.location = '$nextUrl';</script>");
        }
    }

    // if exit access token
    /**
     * @return bool|mixed
     */
    public function getAuthorizedToken() {
        $token = false;
        if (!is_null(Mage::getSingleton('core/session')->getAccessToken())) {
            $token = unserialize(Mage::getSingleton('core/session')->getAccessToken());
        }
        return $token;
    }

    // if not exit access token
    /**
     *
     */
    public function getAuthorization() {
        $redirectUrl = Mage::app()->getStore()->getBaseUrl() . 'sociallogin/vklogin/user';
        $scope = 'offline,wall,friends,email';
        $vklogin = Mage::getModel('sociallogin/vklogin')->getVk();
        $url = $vklogin->getAuthorizeUrl($scope, $redirectUrl);
        return $this->_redirectUrl($url);
    }

    /**
     * @return mixed
     */
    protected function _loginPostRedirect() {
        $selecturl = Mage::getStoreConfig(('sociallogin/general/select_url'), Mage::app()->getStore()->getId());
        if ($selecturl == 0) return Mage::getUrl('customer/account');
        if ($selecturl == 2) return Mage::getUrl();
        if ($selecturl == 3) return Mage::getSingleton('core/session')->getSocialCurrentpage();
        if ($selecturl == 4) return Mage::getStoreConfig(('sociallogin/general/custom_page'), Mage::app()->getStore()->getId());
        if ($selecturl == 1 && Mage::helper('checkout/cart')->getItemsCount() != 0) return Mage::getUrl('checkout/cart'); else return Mage::getUrl();
    }
}