<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_CommentOnReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_CommentOnReview_Helper_Data
 */
class MageWorkshop_CommentOnReview_Helper_Data extends Mage_Core_Helper_Abstract
{
    const COMMENT_XML_PATH_MODULE_ENABLE   = 'mageworkshop_commentonreview/settings/enable';
    const COMMENT_XML_PATH_AUTO_APPROVE    = 'mageworkshop_commentonreview/settings/auto_approve';
    const COMMENT_XML_PATH_MIN_SYMBOLS     = 'mageworkshop_commentonreview/settings/min_symbols';
    const COMMENT_XML_PATH_MAX_SYMBOLS     = 'mageworkshop_commentonreview/settings/max_symbols';
    const COMMENT_XML_PATH_EXPANDER        = 'mageworkshop_commentonreview/settings/expander';
    const COMMENT_XML_PATH_NICKNAME_SUFFIX = 'mageworkshop_commentonreview/settings/nickname_suffix';

    const COMMENT_XML_PATH_CAPTCHA_ENABLED     = 'mageworkshop_commentonreview/captcha/enabled';
    const COMMENT_XML_PATH_CAPTCHA_PUBLIC_KEY  = 'mageworkshop_commentonreview/captcha/public_key';
    const COMMENT_XML_PATH_CAPTCHA_PRIVATE_KEY = 'mageworkshop_commentonreview/captcha/private_key';

    const COMMENT_XML_PATH_EMAIL_TEMPLATE_ADMIN    = 'mageworkshop_commentonreview/email_admin_notify/template';
    const COMMENT_XML_PATH_EMAIL_RECEIVER_ADMIN    = 'mageworkshop_commentonreview/email_admin_notify/receiver';
    const COMMENT_XML_PATH_EMAIL_SENDER_ADMIN      = 'mageworkshop_commentonreview/email_admin_notify/sender';
    const COMMENT_XML_PATH_EMAIL_COPY_TO_ADMIN     = 'mageworkshop_commentonreview/email_admin_notify/copy_to';
    const COMMENT_XML_PATH_EMAIL_COPY_METHOD_ADMIN = 'mageworkshop_commentonreview/email_admin_notify/copy_method';
    const COMMENT_XML_PATH_EMAIL_ENABLED_ADMIN     = 'mageworkshop_commentonreview/email_admin_notify/enabled';

    const COMMENT_XML_PATH_EMAIL_TEMPLATE_FOR_CUSTOMER      = 'mageworkshop_commentonreview/email_customer_notify/template';
    const COMMENT_XML_PATH_EMAIL_SENDER_FOR_CUSTOMER        = 'mageworkshop_commentonreview/email_customer_notify/sender';
    const COMMENT_XML_PATH_EMAIL_BLIND_COPY_TO_FOR_CUSTOMER = 'mageworkshop_commentonreview/email_customer_notify/blind_copy_to';
    const COMMENT_XML_PATH_EMAIL_ENABLED_FOR_CUSTOMER       = 'mageworkshop_commentonreview/email_customer_notify/enabled';
    const COMMENT_UNINSTALL_PATH = 'mageworkshop_commentonreview/uninstall';
    const COMMENT_PACKAGE_FILE = 'CommentOnReview';

    const SALT_REPLY = 'commentonreview';
    const MODULE_NAME = 'MageWorkshop_CommentOnReview';

    /** @var null $_isReCaptchaEnabled */
    protected $_isReCaptchaEnabled = null;

    /**
     * Get path to js folder for MageWorkshop_CommentOnReview
     *
     * @return string
     */
    public function getCommentReviewJsUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . 'mageworkshop' . DS . 'commentonreview' . DS;
    }

    /**
     * Build nickname for customer
     *
     * @return string
     */
    public function prepareNickname()
    {
        $nickname = $this->__('Guest');

        if (Mage::getSingleton('customer/session')->getCustomerId()) {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            /** @var string $lastname */
            $lastname = $customer->getLastname();
            $nickname = $customer->getFirstname() . $lastname[0];
        }

        return $nickname;
    }

    /**
     * Checking group of current user for auto approve of reply
     *
     * @return bool
     */
    public function getAutoApproveFlag()
    {
        $autoApproveFlag = false;

        $customerGroup     = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $autoApproveGroups = Mage::getStoreConfig(self::COMMENT_XML_PATH_AUTO_APPROVE);

        if ($autoApproveGroups || $autoApproveGroups === "0") {
            $autoApproveFlag = in_array($customerGroup, explode(',', $autoApproveGroups));
        }

        return $autoApproveFlag;
    }

    /**
     * Check if CommentOnReview ReCaptcha is Enabled
     *
     * @return bool
     */
    public function isCommentOnReviewReCaptchaEnabled()
    {
        /** @var MageWorkshop_Core_Helper_Data $mageWorkshopCoreHelper */
        $mageWorkshopCoreHelper = Mage::helper('drcore');

        $this->_isReCaptchaEnabled =
            Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_MODULE_ENABLE)
            && Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_CAPTCHA_ENABLED)
            && $mageWorkshopCoreHelper->pingMageWorkshopReCaptcha(Mage::getStoreConfig(self::COMMENT_XML_PATH_CAPTCHA_PRIVATE_KEY)) === true;

        return $this->_isReCaptchaEnabled;
    }
    
    /**
     * Get review statuses with their codes
     * Method was added for compatibility with Magento versions less than 1.8.0.0
     *
     * @return array
     */
    public function getReviewStatuses()
    {
        return array(
            Mage_Review_Model_Review::STATUS_APPROVED     => $this->__('Approved'),
            Mage_Review_Model_Review::STATUS_PENDING      => $this->__('Pending'),
            Mage_Review_Model_Review::STATUS_NOT_APPROVED => $this->__('Not Approved'),
        );
    }
    
    /**
     * Get review statuses as option array
     * Method was added for compatibility with Magento versions less than 1.8.0.0
     *
     * @return array
     */
    public function getReviewStatusesOptionArray()
    {
        $result = array();
        foreach ($this->getReviewStatuses() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }
        
        return $result;
    }
}