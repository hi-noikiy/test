<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRGeoIp
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DRGeoIp_Model_Observer
{
    /**
     * controller_action_predispatch_adminhtml_catalog_product_review_post - global event
     */
    public function  sendNewReviewEmailToAdmin($observer)
    {
        /**
         * @var MageWorkshop_DetailedReview_Model_Review $review
         */
        $review = $observer->getReview();
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::getStoreConfig('drgeoip/settings/enable')) {
            return $review;
        }
        if (!Mage::helper('detailedreview')->canSendNewReviewEmail($storeId)) {
            return $review;
        }

        $storeEmailAddresses = Mage::getStoreConfig('trans_email');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        $customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        } elseif ($email = $review->getCustomerEmail()) {
            $customerEmail = $email;
        } else {
            $customerEmail = 'n/a';
        }
        $receiver = 'ident_' . Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_RECEIVER, $storeId);
        $recipientName = $storeEmailAddresses[$receiver]['name'];
        $recipientEmail = $storeEmailAddresses[$receiver]['email'];

        $remoteAddr = Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR')
            ? Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR')
            : Mage::helper('core/http')->getRemoteAddr();

        $geoIp = new MageWorkshop_DRGeoIp_Model_GeoIp($remoteAddr);
        if ($review->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED) {
            $action = Mage::helper('drcore')->__('check review content');
        } else {
            $action = Mage::helper('drcore')->__('approve review');
        }
        $data = array(
            'sender' => Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_SENDER, $storeId),
            'recipient_name' => $recipientName,
            'recipient_email' => $recipientEmail,
            'copy_to_path' => MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_COPY_TO ,
            'copy_method' => Mage::getStoreConfig(MageWorkshop_DetailedReview_Model_Review::XML_PATH_EMAIL_COPY_METHOD, $storeId),
            'template_id' => Mage::getStoreConfig('drgeoip/admin_email_notify/template', $storeId),
            'template_params' => array(
                'review'      => $review,
                'product'     => Mage::getModel('catalog/product')->load($review->getEntityPkValue()),
                'review_link' => Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product_review/edit/', array('id' => $review->getId())),
                'action' => $action,
                'geo_ip'            => $geoIp,
                'customer_email' => $customerEmail,
                'recipient_name' => $recipientName
            )

        );

        $mailersData = new MageWorkshop_DetailedReview_Model_Review_MailersData($data);
        $review->sendEmail($mailersData);
    }

    public function checkIfModuleEnabled($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        $helper->checkIfModuleEnabled(
            $moduleContainer,
            MageWorkshop_DRGeoIp_Helper_Data::DRGEOIP_MODULE_NAME,
            MageWorkshop_DRGeoIp_Helper_Data::DRGEOIP_XML_PATH_MODULE_ENABLE
        );
    }

    public function enableModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->enableModule(
            $moduleConfig,
            MageWorkshop_DRGeoIp_Helper_Data::DRGEOIP_MODULE_NAME,
            MageWorkshop_DRGeoIp_Helper_Data::DRGEOIP_XML_PATH_MODULE_ENABLE
        );
    }

    public function uninstallModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->uninstallModule(
            $moduleConfig,
            MageWorkshop_DRGeoIp_Helper_Data::DRGEOIP_MODULE_NAME,
            MageWorkshop_DRGeoIp_Helper_Data::DRGEOIP_PACKAGE_FILE,
            MageWorkshop_DRGeoIp_Helper_Data::DRGEOIP_UNINSTALL_PATH
        );
    }
}

