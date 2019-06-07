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
 * Class MageWorkshop_CommentOnReview_Model_Observer
 */
class MageWorkshop_CommentOnReview_Model_Observer
{
    /**
     * Send Email to administrator if new reply was save successful
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendNewReplyEmailToAdmin(Varien_Event_Observer $observer)
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $reply */
        $reply = $observer->getEvent()->getData('reply');

        if (!Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_ENABLED_ADMIN)) {
            return;
        }

        $storeEmailAddresses = Mage::getStoreConfig('trans_email');

        $customerEmail  = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        $storeId        = Mage::app()->getStore()->getId();
        $receiver       = 'ident_' . Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_RECEIVER_ADMIN, $storeId);
        $recipientName  = $storeEmailAddresses[$receiver]['name'];
        $recipientEmail = $storeEmailAddresses[$receiver]['email'];

        /** @var MageWorkshop_CommentOnReview_Helper_Data $helper */
        $helper = Mage::helper('mageworkshop_commentonreview');

        $action = $reply->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED
            ? $helper->__('check reply content')
            : $helper->__('approve reply');

        /** @var Mage_Adminhtml_Helper_Data $adminhtmlHelper */
        $adminhtmlHelper = Mage::helper('adminhtml');

        $data = array(
            'sender'          => Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_SENDER_ADMIN, $storeId),
            'recipient_name'  => $recipientName,
            'recipient_email' => $recipientEmail,
            'copy_to_path'    => MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_COPY_TO_ADMIN,
            'copy_method'     => Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_COPY_METHOD_ADMIN, $storeId),
            'template_id'     => Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_TEMPLATE_ADMIN, $storeId),
            'template_params' => array(
                'review'         => $reply,
                'reply_link'     => $adminhtmlHelper->getUrl('adminhtml/mageworkshop_commentonreview_comment/edit/', array('id' => $reply->getId())),
                'action'         => $action,
                'customer_email' => $customerEmail,
                'recipient_name' => $recipientName
            )
        );

        if ($reply->getStatusId() != Mage_Review_Model_Review::STATUS_APPROVED) {
            $data['template_params']['approve_reply_link'] = Mage::getUrl(
                'commentonreview/reply/approve',
                array(
                    '_secure'  => Mage::app()->getStore()->isCurrentlySecure(),
                    'reply_id' => $reply->getId(),
                    'hash'     => base64_encode($reply->getId() . MageWorkshop_CommentOnReview_Helper_Data::SALT_REPLY)
                )
            );
        }

        /** @var MageWorkshop_DetailedReview_Model_Review_MailersData $mailersData */
        $mailersData = Mage::getModel('detailedreview/review_mailersData')->setData($data);
        $reply->sendEmail($mailersData);
    }

    /**
     * Send Email to customer if new reply was save successful
     *
     * @param $observer Varien_Event_Observer
     */
    public function sendNewReplyEmailToCustomer(Varien_Event_Observer $observer)
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $reply */
        $reply = $observer->getEvent()->getData('reply');

        if (!Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_ENABLED_FOR_CUSTOMER)) {
            return;
        }

        if (!$customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail()) {
            return;
        }

        $data = array(
            'sender'          => Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_SENDER_FOR_CUSTOMER),
            'recipient_name'  => $reply->getNickname(),
            'recipient_email' => $customerEmail,
            'copy_to_path'    => MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_BLIND_COPY_TO_FOR_CUSTOMER,
            'copy_method'     => 'bcc',
            'template_id'     => Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_EMAIL_TEMPLATE_FOR_CUSTOMER),
            'template_params' => array(
                'is_approved'   => $reply->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED,
                'customer_name' => $reply->getNickname()
            )
        );

        /** @var MageWorkshop_DetailedReview_Model_Review_MailersData $mailersData */
        $mailersData = Mage::getModel('detailedreview/review_mailersData')->setData($data);
        $reply->sendEmail($mailersData);
    }

    public function checkIfModuleEnabled($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        $helper->checkIfModuleEnabled(
            $moduleContainer,
            MageWorkshop_CommentOnReview_Helper_Data::MODULE_NAME,
            MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_MODULE_ENABLE
        );
    }

    public function enableModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->enableModule(
            $moduleConfig,
            MageWorkshop_CommentOnReview_Helper_Data::MODULE_NAME,
            MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_MODULE_ENABLE
        );
    }

    public function uninstallModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->uninstallModule(
            $moduleConfig,
            MageWorkshop_CommentOnReview_Helper_Data::MODULE_NAME,
            MageWorkshop_CommentOnReview_Helper_Data::COMMENT_PACKAGE_FILE,
            MageWorkshop_CommentOnReview_Helper_Data::COMMENT_UNINSTALL_PATH
        );
    }


    /**
     * Check Mageworkshop_Commentonreview Admin Configuration
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminSystemConfigChangedSectionMageworkshopCommentonreview(Varien_Event_Observer $observer)
    {
        /** @var MageWorkshop_Core_Helper_Data $mageWorkshopCoreHelper */
        $mageWorkshopCoreHelper = Mage::helper('drcore');

        /** @var Mage_Adminhtml_Model_Config_Data $configDataModel */
        $configDataModel = Mage::getSingleton('adminhtml/config_data');

        if (Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_CAPTCHA_ENABLED)) {
            $reCaptchaPingResult = $mageWorkshopCoreHelper->pingMageWorkshopReCaptcha(
                Mage::getStoreConfig(MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_CAPTCHA_PRIVATE_KEY)
            );

            if (!$reCaptchaPingResult) {
                Mage::getConfig()->saveConfig(
                    MageWorkshop_CommentOnReview_Helper_Data::COMMENT_XML_PATH_CAPTCHA_ENABLED,
                    '0',
                    $configDataModel->getScope(),
                    $configDataModel->getScopeId()
                );

                Mage::log(
                    'MageWorkshop_CommentOnReview ReCaptcha: Google ReCaptcha connection incorrect. '
                    . 'Check "Private Key" configuration by path '
                    . 'System -> Configuration -> MageWorkshop -> Comment On Review -> Captcha Options.',
                    Zend_Log::ERR,
                    'exception.log'
                );

                Mage::getSingleton('core/session')->addError(
                    $mageWorkshopCoreHelper->__(
                        'Captcha could not be enabled. Please check "Private Key" configuration.'
                    )
                );
            }
        }
    }
}