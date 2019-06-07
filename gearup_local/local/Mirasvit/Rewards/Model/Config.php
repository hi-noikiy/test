<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Model_Config
{
    const BEHAVIOR_TRIGGER_SIGNUP = 'signup';
    const BEHAVIOR_TRIGGER_LOGIN = 'customer_login';
    const BEHAVIOR_TRIGGER_ORDER = 'order';
    const BEHAVIOR_TRIGGER_VOTE = 'vote';
    const BEHAVIOR_TRIGGER_SEND_LINK = 'send_link';
    const BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP = 'newsletter_signup';
    const BEHAVIOR_TRIGGER_TAG = 'tag';
    const BEHAVIOR_TRIGGER_REVIEW = 'review';
    const BEHAVIOR_TRIGGER_BIRTHDAY = 'birthday';
    const BEHAVIOR_TRIGGER_INACTIVITY = 'inactivity';
    const BEHAVIOR_TRIGGER_FACEBOOK_LIKE = 'facebook_like';
    const BEHAVIOR_TRIGGER_FACEBOOK_SHARE = 'facebook_share';
    const BEHAVIOR_TRIGGER_TWITTER_TWEET = 'twitter_tweet';
    const BEHAVIOR_TRIGGER_GOOGLEPLUS_ONE = 'googleplus_one';
    const BEHAVIOR_TRIGGER_PINTEREST_PIN = 'pinterest_pin';
    const BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP = 'referred_customer_signup';
    //    const BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_FIRST_ORDER = 'referred_customer_first_order';
    const BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_ORDER = 'referred_customer_order';
    const TYPE_PRODUCT = 'product';
    const TYPE_CART = 'cart';
    const TYPE_BEHAVIOR = 'behavior';
    const NOTIFICATION_POSITION_ACCOUNT_REWARDS = 'account_rewards';
    const NOTIFICATION_POSITION_ACCOUNT_REFERRALS = 'account_referrals';
    const NOTIFICATION_POSITION_CART = 'cart';
    const NOTIFICATION_POSITION_CHECKOUT = 'checkout';
    const REFERRAL_STATUS_SENT = 'sent';
    const REFERRAL_STATUS_VISITED = 'visited';
    const REFERRAL_STATUS_SIGNUP = 'signup';
    const REFERRAL_STATUS_MADE_ORDER = 'referred_customer_order';
    const TOTAL_TYPE_SUBTOTAL_TAX = 'subtotal_tax';
    const TOTAL_TYPE_SUBTOTAL_TAX_SHIPPING = 'subtotal_tax_shipping';

    const EARNING_STYLE_GIVE = 'earning_style_give';
    const EARNING_STYLE_AMOUNT_SPENT = 'earning_style_amount_spent';
    const EARNING_STYLE_QTY_SPENT = 'earning_style_qty_spent';
    const EARNING_STYLE_AMOUNT_PRICE = 'earning_style_amount_price';
    const EARNING_STYLE_PERCENT_PRICE = 'earning_style_percent_price';

    // Const Rewards and Coupons intersection modes
    const COUPONS_ENABLED = 'coupons_enabled';
    const COUPONS_DISABLED_WARNED = 'coupons_disabled_warned';
    const COUPONS_DISABLED_HIDDEN = 'coupons_disabled_hidden';

    // Const Points refreshing stages
    const STAGE_QUOTE_SAVE_FRONTEND = 'quote_save_frontend';
    const STAGE_QUOTE_SAVE_BACKEND = 'quote_save_backend';
    const STAGE_ORDER_PREDISPATCH = 'order_predispatch';

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getGeneralPointUnitName($store = null)
    {
        return Mage::getStoreConfig('rewards/general/point_unit_name', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return int
     */
    public function getGeneralExpiresAfterDays($store = null)
    {
        return Mage::getStoreConfig('rewards/general/expires_after_days', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsEarnAfterInvoice($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_earn_after_invoice', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsEarnAfterShipment($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_earn_after_shipment', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralEarnInStatuses($store = null)
    {
        $value = Mage::getStoreConfig('rewards/general/earn_in_statuses', $store);

        return explode(',', $value);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsCancelAfterRefund($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_cancel_after_refund', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsRestoreAfterRefund($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_restore_after_refund', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsEarnShipping($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_earn_shipping', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsSpendShipping($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_spend_shipping', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsAllowZeroOrders($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_allow_zero_orders', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsAllowRewardsAndCoupons($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_allow_rewards_and_coupons', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getGeneralIsAllowPointsAsMoney($store = null)
    {
        return Mage::getStoreConfig('rewards/general/is_allow_points_as_money', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getNotificationSenderEmail($store = null)
    {
        return Mage::getStoreConfig('rewards/notification/sender_email', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getNotificationBalanceUpdateEmailTemplate($store = null)
    {
        return Mage::getStoreConfig('rewards/notification/balance_update_email_template', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getNotificationPointsExpireEmailTemplate($store = null)
    {
        return Mage::getStoreConfig('rewards/notification/points_expire_email_template', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getNotificationSendBeforeExpiringDays($store = null)
    {
        return Mage::getStoreConfig('rewards/notification/send_before_expiring_days', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getReferralIsActive($store = null)
    {
        return Mage::getStoreConfig('rewards/referral/is_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getReferralInvitationEmailTemplate($store = null)
    {
        return Mage::getStoreConfig('rewards/referral/invitation_email_template', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getDisplayCheckout($store = null)
    {
        return Mage::getStoreConfig('rewards/display/checkout', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getDisplayCart($store = null)
    {
        return Mage::getStoreConfig('rewards/display/cart', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return array
     */
    public function getDisplayBehaviourNotifications($store = null)
    {
        if (!$notifications = Mage::getStoreConfig('rewards/display/behaviour_notifications', $store)) {
            return array();
        }
        return explode(',', $notifications);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return array
     */
    public function getAdvancedDisabledRefreshStages($store = null)
    {
        if (!$stages = Mage::getStoreConfig('rewards/advanced/disable_points_refresh', $store)) {
            return array();
        }

        return explode(',', $stages);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function getAdvancedObserverRefreshPoints($store = null)
    {
        return Mage::getStoreConfig('rewards/advanced/observer_points_refresh', $store);
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getAdvancedExcludeRefreshPath($store = null)
    {
        return Mage::getStoreConfig('rewards/advanced/exclude_request_url', $store);
    }

    /**
     * By default we must allow including discounts in total.
     * Otherwise we don't apply discount on the last step of paypal express checkout (in the result order)
     * @var bool $_calculateTotalFlag
     */
    protected $_calculateTotalFlag = true;

    /**
     * @return bool
     */
    public function getCalculateTotalFlag()
    {
        return $this->_calculateTotalFlag;
    }

    /**
     * @param bool $value
     * @return Mirasvit_Rewards_Model_Config
     */
    public function setCalculateTotalFlag($value)
    {
        $this->_calculateTotalFlag = $value;

        return $this;
    }

    /**
     * @var bool $_spendTotalAppliedFlag
     */
    protected $_spendTotalAppliedFlag = false;

    /**
     * @return bool
     */
    public function getSpendTotalAppliedFlag()
    {
        return $this->_spendTotalAppliedFlag;
    }

    /**
     * @param bool $value
     * @return Mirasvit_Rewards_Model_Config
     */
    public function setSpendTotalAppliedFlag($value)
    {
        $this->_spendTotalAppliedFlag = $value;

        return $this;
    }

    /**
     * @var bool $_spendTotalAppliedFlag
     */
    protected $_quoteSaveFlag = false;

    /**
     * @return bool
     */
    public function getQuoteSaveFlag()
    {
        return $this->_quoteSaveFlag;
    }

    /**
     * @param bool $value
     * @return Mirasvit_Rewards_Model_Config
     */
    public function setQuoteSaveFlag($value)
    {
        $this->_quoteSaveFlag = $value;

        return $this;
    }
}
