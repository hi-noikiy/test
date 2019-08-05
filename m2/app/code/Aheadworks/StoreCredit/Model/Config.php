<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Aheadworks\StoreCredit\Model\Config
 */
class Config
{
    /**#@+
     * Constants for config path.
     */
    const XML_PATH_AW_STORECREDIT_GENERAL_IS_REFUND_AUTOMATICALLY =
        'aw_storecredit/general/is_storecredit_refund_automatically';
    const XML_PATH_AW_STORECREDIT_GENERAL_IS_APPLYING_STORECREDIT_ON_TAX =
        'aw_storecredit/general/is_applying_storecredit_on_tax';
    const XML_PATH_AW_STORECREDIT_GENERAL_IS_APPLYING_STORECREDIT_ON_SHIPPING =
        'aw_storecredit/general/is_applying_storecredit_on_shipping';
    const XML_PATH_AW_STORECREDIT_FRONTEND_IS_TOP_LINK =
        'aw_storecredit/frontend/is_storecredit_balance_top_link';
    const XML_PATH_AW_STORECREDIT_FRONTEND_IS_HIDE_IF_BALANCE_EMPTY =
        'aw_storecredit/frontend/is_hide_if_storecredit_balance_empty';
    const XML_PATH_AW_STORECREDIT_IS_DISPLAY_DISCOUNT_PRICE =
        'aw_storecredit/frontend/is_display_prices_by_storecredit';
    const XML_PATH_AW_STORECREDIT_SENDER_IDENTITY =
        'aw_storecredit/notifications/email_sender';
    const XML_PATH_AW_STORECREDIT_BALANCE_UPDATE_TEMPLATE_IDENTITY =
        'aw_storecredit/notifications/balance_update_template';
    const XML_PATH_AW_STORECREDIT_BALANCE_UPDATE_ACTIONS =
        'aw_storecredit/notifications/balance_update_actions';
    const XML_PATH_AW_STORECREDIT_SUBSCRIBE_CUSTOMERS_TO_NOTIFICATIONS_BY_DEFAULT =
        'aw_storecredit/notifications/is_subscribe_customers_to_notifications_by_default';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve config value for Refund to Store Credit Automatically
     *
     * @return boolean
     */
    public function isStoreCreditRefundAutomatically()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_STORECREDIT_GENERAL_IS_REFUND_AUTOMATICALLY
        );
    }

    /**
     * Retrieve config value for Allow applying Store Credit on Tax
     *
     * @param null|int $websiteId
     * @return boolean
     */
    public function isApplyingStoreCreditOnTax($websiteId = null)
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_STORECREDIT_GENERAL_IS_APPLYING_STORECREDIT_ON_TAX,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Allow applying Store Credit on Shipping
     *
     * @param null|int $websiteId
     * @return boolean
     */
    public function isApplyingStoreCreditOnShipping($websiteId = null)
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_STORECREDIT_GENERAL_IS_APPLYING_STORECREDIT_ON_SHIPPING,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Is Store Credit Balance Top Link enable on Frontend
     *
     * @return boolean
     */
    public function isStoreCreditBalanceTopLinkAtFrontend()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_STORECREDIT_FRONTEND_IS_TOP_LINK,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for Hide the top-link if Store Credit balance is empty
     *
     * @return boolean
     */
    public function isHideIfStoreCreditBalanceEmpty()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_STORECREDIT_FRONTEND_IS_HIDE_IF_BALANCE_EMPTY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for Display prices discounted by available Store Credit on Frontend
     *
     * @return boolean
     */
    public function isDisplayPriceWithDiscount()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_STORECREDIT_IS_DISPLAY_DISCOUNT_PRICE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get email sender
     *
     * @param null|int $websiteId
     * @return string
     */
    public function getEmailSender($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_STORECREDIT_SENDER_IDENTITY,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve Subscribe customers to Store Credit notifications by default
     *
     * @param null|int $websiteId
     * @return boolean
     */
    public function isSubscribeCustomersToNotificationsByDefault($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_STORECREDIT_SUBSCRIBE_CUSTOMERS_TO_NOTIFICATIONS_BY_DEFAULT,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get email sender name
     *
     * @param null|int $websiteId
     * @return string
     */
    public function getEmailSenderName($websiteId = null)
    {
        $sender = $this->getEmailSender($websiteId);

        return $this->scopeConfig->getValue(
            'trans_email/ident_' . $sender . '/name',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get balance update email template
     *
     * @param null|int $storeId
     * @return string
     */
    public function getBalanceUpdateEmailTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_STORECREDIT_BALANCE_UPDATE_TEMPLATE_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get balance update actions
     *
     * @param null|int $storeId
     * @return string
     */
    public function getBalanceUpdateActions($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_STORECREDIT_BALANCE_UPDATE_ACTIONS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
