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

/**
 * Class Magestore_Sociallogin_Model_Amazon
 */
class Magestore_Sociallogin_Model_Amazon extends Mage_Core_Model_Abstract
{
    /**
     * @param $authcode
     * @return bool
     */
    public function getAccessTokenFromAuthCode($authcode) {
        // setup a request to the profile endpoint
        $c = curl_init('https://api.amazon.com/auth/o2/token');
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, "grant_type=authorization_code&code={$authcode}&client_id={Mage::helper('sociallogin')->getAmazonId()}&client_secret={Mage::helper('sociallogin')->getAmazonSecret()}");
//        curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;charset=UTF-8'));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        // make the request
        $r = curl_exec($c);

        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);

        // decode the response
        $d = json_decode($r);
        if ($status != 200 or $d->error) {
            var_dump($d);
            return false;
        }
        return $d->access_token;
    }

    /**
     * Requests Amazon User-Profile from access_token
     *
     * @param string $access_token
     * @return object $profile Amazon-User-Profile
     */
    public function getUserProfileFromAccessToken($access_token) {
        // setup a request to the profile endpoint
        $c = curl_init('https://api.amazon.com/user/profile');
        curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        // make the request
        $r = curl_exec($c);
        curl_close($c);
        // decode the response
        $d = json_decode($r);
        return $d;
    }
}
  
