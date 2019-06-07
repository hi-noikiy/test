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
class Magestore_Sociallogin_FbloginController extends Mage_Core_Controller_Front_Action
{

    /**
     *
     */
    public function loginAction()
    {
        $isAuth = $this->getRequest()->getParam('auth');
        $facebook = Mage::getModel('sociallogin/fblogin')->newFacebook();
        $userId = $facebook->getUser();

        if ($this->getRequest()->getParam('error_reason') == 'user_denied') {
            $output = ("<script>window.close();</script>");
            $this->getResponse()->setBody($output);
            return;
        } elseif ($isAuth && !$userId) {
            $loginUrl = $facebook->getLoginUrl(array('scope' => 'email'));
            $output = "<script type='text/javascript'>top.location.href = '$loginUrl';</script>";
            $this->getResponse()->setBody($output);
            return;
        }
        $user = Mage::getModel('sociallogin/fblogin')->getFbUser();

        if ($isAuth && $user) {
            $store_id = Mage::app()->getStore()->getStoreId();//add
            $website_id = Mage::app()->getStore()->getWebsiteId();//add

            $lastname = '';
            if ($user['last_name'] != '') {
                $lastname = $user['last_name'];
            } else {
                $lastname = $user['first_name'];
            }
            $data = array('firstname' => $user['first_name'], 'lastname' => $lastname, 'email' => $user['email']);
            
            //$data = array('firstname' => $user['first_name'], 'lastname' => $user['last_name'], 'email' => $user['email']);
            
            if ($data['email']) {
                $customer = Mage::helper('sociallogin')->getCustomerByEmail($data['email'], $website_id);//add edition
                if (!$customer || !$customer->getId()) {
                    //Login multisite
                    $customer = Mage::helper('sociallogin')->createCustomerMultiWebsite($data, $website_id, $store_id);
                    if (Mage::getStoreConfig(('sociallogin/general/send_newemail'), Mage::app()->getStore()->getId())) $customer->sendNewAccountEmail('registered', '', Mage::app()->getStore()->getId());
                    if (Mage::getStoreConfig('sociallogin/fblogin/is_send_password_to_customer')) {
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
            } else {
                $message = $this->__('You provided a email invalid!');
                Mage::getSingleton('core/session')->addError($message);
                $this->getResponse()->setBody("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . Mage::app()->getStore()->getBaseUrl() . "\"} window.close();</script>");
            }
        }
    }

    /**
     * @return mixed
     */
    protected function _loginPostRedirect()
    {
        $selecturl = Mage::getStoreConfig(('sociallogin/general/select_url'), Mage::app()->getStore()->getId());
        if ($selecturl == 0) return Mage::getUrl('customer/account');
        if ($selecturl == 2) return Mage::getUrl();
        if ($selecturl == 3) return Mage::getSingleton('core/session')->getSocialCurrentpage();
        if ($selecturl == 4) return Mage::getStoreConfig(('sociallogin/general/custom_page'), Mage::app()->getStore()->getId());
        if ($selecturl == 1 && Mage::helper('checkout/cart')->getItemsCount() != 0) return Mage::getUrl('checkout/cart'); else return Mage::getUrl();
    }


}