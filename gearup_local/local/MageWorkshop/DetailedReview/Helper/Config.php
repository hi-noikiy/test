<?php

/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_DetailedReview_Helper_Config extends Mage_Core_Helper_Abstract
{
    const DRCATEGORYRATINGS_XML_PATH_MODULE_ENABLED = 'drcategoryratings/settings/enable';

    const DETAILEDREVIEW_MODULE_NAME = 'MageWorkshop_DetailedReview';
    const DETAILEDREVIEW_XML_PATH_MODULE_ENABLED = 'detailedreview/settings/enable';
    const DETAILEDREVIEW_UNINSTALL_PATH = 'detailedreview/uninstall';
    const DETAILEDREVIEW_PACKAGE_FILE = 'DetailedReview';
    const DETAILEDREVIEW_PRODUCT_ATTR_INDEXER_CODE = 'detailedreview_product_attribute';
    const DETAILEDREVIEW_VERIFIED_BUYER_INDEXER_CODE = 'detailedreview_verified_buyer';

    const DETAILEDREVIEW_XML_PATH_EMAIL_COMPLAINT_TEMPLATE_ADMIN    = 'detailedreview/email_admin_notify_complaint/template';
    const DETAILEDREVIEW_XML_PATH_EMAIL_COMPLAINT_RECEIVER_ADMIN    = 'detailedreview/email_admin_notify_complaint/receiver';
    const DETAILEDREVIEW_XML_PATH_EMAIL_COMPLAINT_SENDER_ADMIN      = 'detailedreview/email_admin_notify_complaint/sender';
    const DETAILEDREVIEW_XML_PATH_EMAIL_COMPLAINT_COPY_TO_ADMIN     = 'detailedreview/email_admin_notify_complaint/copy_to';
    const DETAILEDREVIEW_XML_PATH_EMAIL_COMPLAINT_COPY_METHOD_ADMIN = 'detailedreview/email_admin_notify_complaint/copy_method';
    const DETAILEDREVIEW_XML_PATH_EMAIL_COMPLAINT_ENABLED_ADMIN     = 'detailedreview/email_admin_notify_complaint/enabled';

    const DETAILEDREVIEW_XML_PATH_ALLOW_PRODUCT_PREVIEW_FOR_FORM     = 'detailedreview/show_review_info_settings/allow_product_preview';
    const DETAILEDREVIEW_XML_PATH_SEPARATE_FORM                      = 'detailedreview/settings/review_form_separate';
    const DETAILEDREVIEW_XML_PATH_CAPTCHA_ENABLED                    = 'detailedreview/captcha/enabled';
    const DETAILEDREVIEW_XML_PATH_CAPTCHA_PUBLIC_KEY                 = 'detailedreview/captcha/public_key';
    const DETAILEDREVIEW_XML_PATH_CAPTCHA_PRIVATE_KEY                = 'detailedreview/captcha/private_key';
    const DETAILEDREVIEW_XML_PATH_EMAIL_FIELD                        = 'detailedreview/settings_customer/email_field';
    const DETAILEDREVIEW_XML_PATH_WRITE_REVIEW_ONCE                  = 'detailedreview/settings_customer/write_review_once';
    const DETAILEDREVIEW_XML_PATH_ONLY_VERIFIED_BUYER                = 'detailedreview/settings_customer/only_verified_buyer';
    const DETAILEDREVIEW_XML_PATH_ENABLE                             = 'detailedreview/settings/enable';
    const DETAILEDREVIEW_XML_PATH_HONEYPOT                           = 'detailedreview/settings/show_honeypot';
    const DETAILEDREVIEW_XML_PATH_SUBMIT_REVIEW_AJAX                 = 'detailedreview/settings/submit_review_ajax';
    const DETAILEDREVIEW_XML_PATH_ENABLE_RECOMMENDED_PRODUCT         = 'detailedreview/social_share_optios/recommended_product';
    const DETAILEDREVIEW_XML_PATH_RECOMMENDED_PRODUCT_OPTION         = 'detailedreview/social_share_optios/recommend_qty_available';
    const DETAILEDREVIEW_XML_PATH_SHARE_EMAIL_TEMPLATE               = 'detailedreview/social_share_optios/share_review_template';
    const DETAILEDREVIEW_XML_PATH_BLIND_COPY_TO_FOR_CUSTOMER         = 'detailedreview/social_share_optios/share_review_blind_copy_to';
    const DETAILEDREVIEW_XML_PATH_MAX_IMAGE_SIZE                     = 'detailedreview/image_options/max_image_size';
    const DETAILEDREVIEW_XML_PATH_MIN_IMAGE_WIDTH                    = 'detailedreview/image_options/min_image_width';
    const DETAILEDREVIEW_XML_PATH_MIN_IMAGE_HEIGHT                   = 'detailedreview/image_options/min_image_height';
    const DETAILEDREVIEW_XML_PATH_IMAGE_MAX_COUNT                    = 'detailedreview/image_options/images_max_count';
    const DETAILEDREVIEW_XML_PATH_ALLOW_GUEST_VOTE                   = 'detailedreview/settings_customer/allow_guest_vote';

    const DETAILEDREVIEW_XML_PATH_NICKNAME_MIN                       = 'detailedreview/validation_options/nickname_min';
    const DETAILEDREVIEW_XML_PATH_NICKNAME_MAX                       = 'detailedreview/validation_options/nickname_max';
    const DETAILEDREVIEW_XML_PATH_TITLE_MIN                          = 'detailedreview/validation_options/title_min';
    const DETAILEDREVIEW_XML_PATH_TITLE_MAX                          = 'detailedreview/validation_options/title_max';
    const DETAILEDREVIEW_XML_PATH_DETAIL_MIN                         = 'detailedreview/validation_options/detail_min';
    const DETAILEDREVIEW_XML_PATH_DETAIL_MAX                         = 'detailedreview/validation_options/detail_max';
    const DETAILEDREVIEW_XML_PATH_USER_PROS_MAX                      = 'detailedreview/validation_options/user_pros_max';
    const DETAILEDREVIEW_XML_PATH_GOOD_DETAIL_MAX                    = 'detailedreview/validation_options/good_detail_max';
    const DETAILEDREVIEW_XML_PATH_USER_CONS_MAX                      = 'detailedreview/validation_options/user_cons_max';
    const DETAILEDREVIEW_XML_PATH_NO_GOOD_DETAIL_MAX                 = 'detailedreview/validation_options/no_good_detail_max';
    const DETAILEDREVIEW_XML_PATH_LOCATION_MAX                       = 'detailedreview/validation_options/location_max';

    const DETAILEDREVIEW_XML_PATH_FB_SOCIAL_SHARE                    = 'detailedreview/social_share_optios/share_review_to_facebook';
    const DETAILEDREVIEW_XML_PATH_FACEBOOK_APP_ID                    = 'detailedreview/social_share_optios/facebook_app_id';
    const DETAILEDREVIEW_XML_PATH_FACEBOOK_APP_SECRET                = 'detailedreview/social_share_optios/facebook_app_secret';
    const DETAILEDREVIEW_XML_PATH_BITLY                              = 'detailedreview/social_share_optios/bitly_enabled';
    const DETAILEDREVIEW_XML_PATH_JSTL_JS                            = 'detailedreview/datetime_options/enable_to_set_timezone';

    const DETAILEDREVIEW_XML_PATH_SEO_ENABLED                        = 'detailedreview/seo/enable';
    const DETAILEDREVIEW_XML_PATH_SEO_REVIEW_COUNT                   = 'detailedreview/seo/review_count';
    const DETAILEDREVIEW_XML_PATH_SEO_DETECT_BOT                     = 'detailedreview/seo/detect_bot';

    const DETAILEDREVIEW_XML_PATH_JS_LIB_JQUERY                      = 'detailedreview/javascript_libraries/jquery_enable';
    const DETAILEDREVIEW_XML_PATH_RECENT_REVIEW                      = 'detailedreview/category_options/recent_reviews';


    /**
     * @param mixed $store
     * @return bool
     */
    public static function isDetailedReviewEnabled($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_ENABLE , $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isAjaxSubmit($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_SUBMIT_REVIEW_AJAX , $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isWriteReviewOnce($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_WRITE_REVIEW_ONCE , $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isOnlyVerifiedBuyer($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_ONLY_VERIFIED_BUYER , $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isFormPopup($store = null)
    {
        return Mage::getStoreConfig('detailedreview/settings/review_form_display', $store) === 'popup';
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function canShowProductPreviewInForm($store = null) {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_ALLOW_PRODUCT_PREVIEW_FOR_FORM, $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isCaptchaEnabled($store = null)
    {
        /** @var MageWorkshop_Core_Helper_Data $mageWorkshopCoreHelper */
        $mageWorkshopCoreHelper = Mage::helper('drcore');
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_CAPTCHA_ENABLED, $store) && $mageWorkshopCoreHelper->pingMageWorkshopReCaptcha(Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_CAPTCHA_PRIVATE_KEY, $store));
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isSeparateForm($store = null)
    {
        return Mage::getStoreConfig('detailedreview/settings/review_form_display', $store) === 'separate';
    }

    /**
     * @param mixed $store
     * @return string
     */
    public static function getLanguageStoreCode($store = null)
    {
        return substr(Mage::getStoreConfig('general/locale/code', $store),0,2);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isDRCategoryRatingsEnabled($store = null)
    {
        return Mage::getStoreConfig(self::DRCATEGORYRATINGS_XML_PATH_MODULE_ENABLED, $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function canShowEmailFieldOnFront($store = null)
    {
        return !Mage::getSingleton('customer/session')->isLoggedIn() && Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_EMAIL_FIELD, $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isHoneypotEnabled($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_HONEYPOT, $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function canShowRecommendedProductOption($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_ENABLE_RECOMMENDED_PRODUCT, $store) && Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_RECOMMENDED_PRODUCT_OPTION, $store);
    }

    /**
     * @param mixed $store
     * @return string
     */
    public static function getRecommendedProductOption($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_RECOMMENDED_PRODUCT_OPTION, $store);
    }


    /**
     * @param mixed $store
     * @return string
     */
    public static function getImageMaxSize($store = null)
    {
        $maxConfigSize = Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_MAX_IMAGE_SIZE, $store);
        $maxUploadSize = Mage::helper('detailedreview')->getMaxUploadSize();
        return ($maxConfigSize < $maxUploadSize)? $maxConfigSize : $maxUploadSize;
    }

    /**
     * @param mixed $store
     * @return string
     */
    public static function getImageMinWidth($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_MIN_IMAGE_WIDTH, $store);
    }

    /**
     * @param mixed $store
     * @return string
     */
    public static function getImageMinHeight($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_MIN_IMAGE_HEIGHT, $store);
    }

    /**
     * @param mixed $store
     * @return int
     */
    public static function getMaxImageCount($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_IMAGE_MAX_COUNT, $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public static function isGuestCanVote($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_ALLOW_GUEST_VOTE, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getNicknameMin($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_NICKNAME_MIN, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getNicknameMax($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_NICKNAME_MAX, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getTitleMin($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_TITLE_MIN, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getTitleMax($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_TITLE_MAX, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getDetailMin($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_DETAIL_MIN, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getDetailMax($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_DETAIL_MAX, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getUserProsMax($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_USER_PROS_MAX, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getGoodDetailMax($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_GOOD_DETAIL_MAX, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getUserConsMax($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_USER_CONS_MAX, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getNoGoodDetailMax($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_NO_GOOD_DETAIL_MAX, $store);
    }

    /**
     * @param null $store
     * @return int
     */
    public static function getLocationMax($store = null)
    {
        return (int) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_LOCATION_MAX, $store);
    }

    public static function isFBShare($store = null)
    {
        return (bool) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_FB_SOCIAL_SHARE, $store);
    }
    public static function getFBShareAppId($store = null)
    {
        return trim(Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_FACEBOOK_APP_ID, $store));
    }
    public static function getFBShareAppSecret($store = null)
    {
        return trim(Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_FACEBOOK_APP_SECRET, $store));
    }

    public static function isBitly($store = null)
    {
        return (bool) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_BITLY, $store);
    }

    public static function isJSTLJS($store = null)
    {
        return (bool) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_JSTL_JS, $store);
    }

    public static function isDRjQuery($store = null)
    {
        return (bool) Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_JS_LIB_JQUERY, $store);
    }
    
    /**
     * @param mixed $store
     * @return string
     */
    public static function getRecentReviewOption($store = null)
    {
        return Mage::getStoreConfig(self::DETAILEDREVIEW_XML_PATH_RECENT_REVIEW, $store);
    }
}
