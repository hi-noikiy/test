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

class MageWorkshop_DetailedReview_Model_Review_JsConfig extends Mage_Core_Model_Abstract
{

    public function __construct()
    {
        $this->_init('detailedreview/review_jsConfig');
    }

    public function getJsonConfig()
    {
        if (!($imagesMaxCount = (int)Mage::getStoreConfig('detailedreview/image_options/images_max_count'))) {
            $imagesMaxCount = 1;
        }
        $versionDR = (array)Mage::getConfig()->getNode()->modules->MageWorkshop_DetailedReview->version;
        $helper = Mage::helper('detailedreview');
        $resizeAverage = 32;
        $resizeSeparate = 20;
        $activeRatingImage = $this->getActiveRatingImage(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'detailedreview/');
        $unactiveRatingImage = $this->getUnactiveRatingImage(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'detailedreview/');

        /** @var MageWorkshop_Core_Helper_Data $mageWorkshopCoreHelper */
        $mageWorkshopCoreHelper = Mage::helper('drcore');
        
        $isCaptchaEnabled = Mage::getStoreConfig('detailedreview/captcha/enabled') 
            && $mageWorkshopCoreHelper->pingMageWorkshopReCaptcha(Mage::getStoreConfig('detailedreview/captcha/private_key'));
        
        $configJson = array (
            "isShowPopup" => (int)Mage::helper('detailedreview/config')->isFormPopup(),
            "fancyBoxConfig" => array(
                "autoScale" => true,
                "autoDimensions" => true,
                "helpers" => array(
                    "overlay" => array(
                        "locked" => false
                    )
                )
            ),
            "isCustomerLoggedIn" => Mage::helper('customer')->isLoggedIn(),
            "isGuestAllowToVote" => (int)Mage::getStoreConfig('detailedreview/settings_customer/allow_guest_vote'),
            "isGuestAllowToWrite" => Mage::helper('review')->getIsGuestAllowToWrite(),
            "onlyVerifiedBuyer" => (int)Mage::getStoreConfig('detailedreview/settings_customer/only_verified_buyer'),
            "productIdsAllowReviewUrl" => Mage::getUrl('detailedreview/product/checkbuyerproducts', array( '_secure' => true)),
            'productId' => Mage::registry('current_product')->getId(),
            "imageMaxCount" => $imagesMaxCount,
            "isAjaxSubmit" => (int)Mage::getStoreConfig('detailedreview/settings/submit_review_ajax'),
            "autoApproveFlag" => Mage::helper('detailedreview')->getAutoApproveFlag(),
            "checkLoginUrl" => Mage::getUrl('detailedreview/index/checklogin', array( '_secure' => true)),
            "isStatusApproved" => Mage_Review_Model_Review::STATUS_APPROVED,
            "isCaptchaEnabled" => $isCaptchaEnabled,
            "checkCaptchaUrl" => Mage::getUrl('detailedreview/product/checkCaptcha', array( '_secure' => true)),
            "captchaApiKey" => Mage::getStoreConfig('detailedreview/captcha/public_key'),
            "checkRegistrationUrl" => Mage::getUrl('detailedreview/index/checkregistrate', array( '_secure' => true)),
            "reviewPlaceholder" => '.reviews-placeholder',
            "reviewPlaceholderDR" => '.reviews-placeholder-dr',
            "reviewSubmitButton" => '#review-form .buttons-set button.button:submit',
            "reviewEasyTab" => '#product_tabs_review_tabbed_contents',
            "reviewsBlock" => '.reviews-wrapper',
            "reviewFormButton" => '#review-form button.button',
            "reviewSpinner" => 'review-add-spinner',
            "captchaError" => '.captcha-error',
            "currentImageCount" => 1,
            "dialogClass" => '',
            "dataLoginForm" => '',
            "loginForm" => '#login-form',
            "moreImagesLink" => '#add-more-images',
            "removeImageLink" => '.remove-img',
            "reviewDialog" => '.review-dialog',
            "reviewForm" => '#review-form',
            "reviewVoteRating" => '.review-vote-rating',
            "dateFilter" => '.review-date-filters',
            "dateFilterSpan" => '.top-title',
            "reviewTop" => '.review-top',
            "customerReviews" => '#customer-reviews',
            "backButton" => '#buttonBack',
            "reviewSorts" => '.review-sorts',
            "sortsSpan" => '.top-dropdown-sorts a',
            "sortsLink" => '.sortsLink',
            "dateFilterLink" => '.top-dropdown',
            "openedList" => '.openedList',
            "prosCheckboxes" => '.pros input[type="checkbox"]',
            "consCheckboxes" => '.cons input[type="checkbox"]',
            "isSeparatePage" => (int)Mage::helper('detailedreview/config')->isSeparateForm(),
            "productPage" => Mage::registry('current_product')->getProductUrl(),
            "separatePage" => Mage::getUrl("detailedreview/index/submitpage/", array('product' => Mage::registry('current_product')->getId(), '_secure' => true)),
            "writeReviewOnce" => (int)Mage::getStoreConfig('detailedreview/settings_customer/write_review_once'),
            "checkWriteReviewOnce" => Mage::getUrl('detailedreview/product/checkwriteonce', array( '_secure' => true)),
            "versionDR" => $versionDR[0],
            "activeRatingImage" => $activeRatingImage,
            "unActiveRatingImage" => $unactiveRatingImage,
            "activeImageAverage" => $helper->getResizedImage($activeRatingImage, $resizeAverage),
            "unActiveImageAverage" => $helper->getResizedImage($unactiveRatingImage, $resizeAverage),
            "activeImageSeparate" => $helper->getResizedImage($activeRatingImage, $resizeSeparate),
            "unActiveImageSeparate" => $helper->getResizedImage($unactiveRatingImage, $resizeSeparate),
            "overallRatingItem" => '.overall-raiting ul li',
            "separateRatingStar" => '.separate-rating-star',
            "messages" => array(
                "captchaError" => $helper->__("You have entered wrong captcha."),
                "someError" => $helper->__("Some error has been occurred."),
                "easyTabAlert" => $helper->__("Please, disable \"product's review tab\" in \"EasyTab\" extension options if you want \"Detailed Review\" extension to work correctly with custom reviews block placeholder."),
                "chooseFile" => $helper->__("Choose file"),
                "maxUploadNotify" => $helper->__("You can upload not more than %s images", $imagesMaxCount),
                "onlyVerifiedBuyer" => $helper->__("Only verified buyer can write review"),
                "alreadyReviewed" => $helper->__("Product already reviewed by You"),
                "goBackMessage" => $helper->__("Go back to product page"),
            ),
            "allowSizing" => (int)Mage::getStoreConfig('detailedreview/show_review_form_settings/allow_sizing'),
            "pnotifyPosition" => array(
                "dir1" => "down",
                "dir2" => "left",
                "firstpos1" => 36,
                "firstpos2" => 36
            ),
            "shortTextSize" => 255,
            "shortTextClass" => '.short-text',
            "moreText" => $helper->__("more"),
            "lessText" => $helper->__("hide details"),
            "moreTextClass" => 'view-more',
            "lessTextClass" => 'view-less',
            "moreLink" => 'more-link',
            "chooseImageClass" => 'input.image_field',
            "backLink" => '#separate-go-back',
            "voteValue" => ''
        );

        Mage::dispatchEvent('detailedreview_js_config', $configJson);
        return Mage::helper('core')->jsonEncode($configJson);
    }

    public function getActiveRatingImage($url)
    {
        $imageUrl = $url . 'default/active-star-rwd.png';
        $activeRatingImage = Mage::getStoreConfig('detailedreview/rating_image/active');

        if (empty($activeRatingImage)) {
            return $imageUrl;
        }

        $dir = Mage::getBaseDir('media');
        $path = $dir . DS . 'detailedreview' . DS . $activeRatingImage;

        if (file_exists($path)) {
            $imageUrl = $url . $activeRatingImage;
        }

        return $imageUrl;
    }

    public function getUnactiveRatingImage($url)
    {
        $imageUrl = $url . 'default/unactive-star-rwd.png';
        $unactiveRatingImage = Mage::getStoreConfig('detailedreview/rating_image/unactive');

        if (empty($unactiveRatingImage)) {
            return $imageUrl;
        }

        $dir = Mage::getBaseDir('media');
        $path = $dir . DS . 'detailedreview' . DS . $unactiveRatingImage;

        if (file_exists($path)) {
            $imageUrl = $url . $unactiveRatingImage;
        }

        return $imageUrl;
    }

    public function getCommentComplaintJsConfig() {
        $helper = Mage::helper('detailedreview');
        $configJson = array (
            "complaintUrl" => Mage::getUrl('detailedreview/complaint/save', array('_secure' => Mage::app()->getStore()->isCurrentlySecure())),
            "emptyComplaintId" => $helper->__('Please select complaint'),
            "delayOfMessageDisplaying" => 8000,
            "complaintWrapper" => 'complaint-list-wrapper',
            "complaintIcon" => '.complaint-icon',
            "complaintCancelButton" => '.complaint-cancel-button',
            "complaintSubmitButton" => '.complaint-submit-button',
            "reportAbuse" => '.report-abuse',
            "abuse" => '.abuse-action',
            "reportBtnWrapper" => '.reviews-container .report-button-wrapper',
            "showAsk" => 'show-ask',
            "complaintValueChecked" => '.complaint-value:checked',
            "errorClass" => '.error',
            "replyWrap" => '.reply-wrap',
            "topReview" => '.top-review',
            "complaintHide" => 'complaint-hide',
            "complaintShow" => 'complaint-show',
            "offsetForBetterView" => 200,
            "reviewContainer" => '.reply, .review-dd',
            "reviewId" => '.review-id',
            "queryParams" => array (
                "customer_id" => Mage::getSingleton('customer/session')->getCustomerId(),
                "review_id" => null,
                "complaint_id" => null
                )
        );
        Mage::dispatchEvent('commentcomplaint_js_config', $configJson);
        return Mage::helper('core')->jsonEncode($configJson);
    }

}
