<?php
/**
 * Created by PhpStorm.
 * User: tungquach
 * Date: 24/08/2017
 * Time: 17:28
 */

namespace Beeketing\MagentoCommon\Data;


final class AppSettingKeys
{
    const BETTERCOUPONBOX_KEY = 'beeketing/bettercouponbox/settings';
    const SALESPOP_KEY = 'beeketing/salespop/settings';
    const QUICKFACEBOOKCHAT_KEY = 'beeketing/quickfacebookchat/settings';
    const HAPPYEMAIL_KEY = 'beeketing/happyemail/settings';
    const PERSONALIZEDRECOMMENDATION_KEY = 'beeketing/personalizedrecommendation/settings';
    const CHECKOUTBOOST_KEY = 'beeketing/checkoutboost/settings';
    const BOOSTSALES_KEY = 'beeketing/boostsales/settings';
    const MAILBOT_KEY = 'beeketing/mailbot/settings';
    const COUNTDOWNCART_KEY = 'beeketing/countdowncart/settings';
    const MOBILEWEBBOOST_KEY = 'beeketing/mobilewebboost/settings';

    /**
     * Get all constants in class
     * @return array
     */
    public static function get_constants() {
        $oClass = new \ReflectionClass( __CLASS__ );
        $eventNames = array_values($oClass->getConstants());

        return $eventNames;
    }
}