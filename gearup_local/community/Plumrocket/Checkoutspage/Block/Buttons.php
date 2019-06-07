<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

class Plumrocket_Checkoutspage_Block_Buttons extends Mage_Core_Block_Template
{
    const DEFAULT_FACEBOOK_APP_ID = '273893043469385';

    public function getMessage()
    {
        $variable = '{{store_name}}';
        $message = $this->_getSettings('message');
        if (false !== mb_strpos($message, $variable)) {
            $storeName = $this->_getStoreName();
            $message = str_replace($variable, $storeName, $message);
        }
        return $message;
    }


    protected function _getStoreName()
    {
        return Mage::app()->getStore()->getFrontendName();
    }


    public function isEnabled()
    {
        return $this->_getSettings('enabled');
    }


    protected function _getSettings($field)
    {
        return Mage::getStoreConfig('checkoutspage/social_share/'.$field);
    }


    public function getPrintUrl()
    {
        return $this->getUrl('checkoutspage/order/print', array('order_id' => $this->helper('checkoutspage')->getOrder()->getId()));
    }

    public function getFacebookAppId()
    {
        $configFacebookAppId = $this->_getSettings('facebook_application_id');
        return ! empty($configFacebookAppId) ? $configFacebookAppId : self::DEFAULT_FACEBOOK_APP_ID;
    }
}
