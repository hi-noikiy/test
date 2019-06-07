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
class Magestore_Sociallogin_YaloginController extends Mage_Core_Controller_Front_Action
{

    // url to login
    /**
     *
     */
    public function loginAction() {
        $yalogin = Mage::getModel('sociallogin/yalogin');
        $hasSession = $yalogin->hasSession();
        if ($hasSession == FALSE) {
            $authUrl = $yalogin->getAuthUrl();
            $this->_redirectUrl($authUrl);
        } else {
            $session = $yalogin->getSession();
            $userSession = $session->getSessionedUser();
            $profile = $userSession->loadProfile();
            $emails = $profile->emails;
            $user = array();
            foreach ($emails as $email) {
                if ($email->primary == 1)
                    $user['email'] = $email->handle;
            }
            $user['firstname'] = $profile->givenName;
            $user['lastname'] = $profile->familyName;

            //get website_id and sote_id of each stores
            $store_id = Mage::app()->getStore()->getStoreId();
            $website_id = Mage::app()->getStore()->getWebsiteId();

            $customer = Mage::helper('sociallogin')->getCustomerByEmail($user['email'], $website_id);
            if (!$customer || !$customer->getId()) {
                //Login multisite
                $customer = Mage::helper('sociallogin')->createCustomerMultiWebsite($user, $website_id, $store_id);
                if (Mage::getStoreConfig(('sociallogin/general/send_newemail'), Mage::app()->getStore()->getId())) $customer->sendNewAccountEmail('registered', '', Mage::app()->getStore()->getId());
                if (Mage::getStoreConfig('sociallogin/yalogin/is_send_password_to_customer')) {
                    $customer->sendPasswordReminderEmail();
                }
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
            $this->getResponse()->setBody("<script type=\"text/javascript\">if(navigator.userAgent.match('CriOS')){window.location.href=\"" . $this->_loginPostRedirect() . "\";}else{try{window.opener.location.href=\"" . $this->_loginPostRedirect() . "\";}catch(e){window.opener.location.reload(true);} window.close();}</script>");
            //$this->_redirectUrl(Mage::helper('customer')->getDashboardUrl());
        }

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