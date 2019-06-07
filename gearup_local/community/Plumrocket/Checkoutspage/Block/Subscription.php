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

class Plumrocket_Checkoutspage_Block_Subscription extends Plumrocket_Checkoutspage_Block_Abstract
{

    public function isSubscriptionEnabled()
    {
        return (bool)Mage::getStoreConfig('checkoutspage/subscription/enabled');
    }


    public function isCustomerSubscribed( $customerId )
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer( $customer );
        return ( $subscriber->getSubscriberStatus() == 3 || is_null($subscriber->getSubscriberStatus()) ) ? false : true;
    }


}