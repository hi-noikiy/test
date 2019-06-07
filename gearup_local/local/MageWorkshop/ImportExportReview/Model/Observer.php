<?php

/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ImportExportReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_ImportExportReview_Model_Observer
 */

class MageWorkshop_ImportExportReview_Model_Observer
{
    /**
     * global event - review_save_after
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function refreshLastExportedId(Varien_Event_Observer $observer)
    {
        /** @var Mage_Review_Model_Review $review */
        $review = $observer->getData('object');
        if ($review->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED && $review->dataHasChangedFor('status_id')) {
            $reviewId = $review->getId();
            /** @var MageWorkshop_ImportExportReview_Model_Resource_Sync_Collection $syncStoresCollection */
            $syncStoresCollection = Mage::getResourceModel('mageworkshop_importexportreview/sync_collection');
            /** @var MageWorkshop_ImportExportReview_Model_Sync $syncStore */
            foreach ($syncStoresCollection as $syncStore) {
                if ($syncStore->getLastExportedId() && (int) $syncStore->getLastExportedId() >= (int) $reviewId) {
                    $syncStore->setData('last_exported_id', $reviewId - 1);
                    $syncStore->save();
                }
            }
        }
    }

    /**
     * global event - review_save_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function shareReviewBetweenWebsites(Varien_Event_Observer $observer)
    {
        if (count(Mage::app()->getWebsites()) > 1 && Mage::getStoreConfigFlag('drie/share_website_reviews/enable') && !Mage::app()->getStore()->isAdmin()) {
            /** @var Mage_Review_Model_Review $review */
            $review = $observer->getData('object');
            $storeIds = array();

            foreach (Mage::app()->getStores() as $store) {
                $storeIds[] = $store->getId();
            }

            $review->setStores($storeIds);
        }
    }

    /**
     * generating unique id by taking md5 from title+detail
     *
     * @param Varien_Event_Observer $observer
     */

    public function generateUniqueId(Varien_Event_Observer $observer)
    {
        $review   = $observer->getData('object');
        $product  = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
        /** @var MageWorkshop_ImportExportReview_Helper_ImportExport $helper */
        $helper   = Mage::helper('mageworkshop_importexportreview/importExport');
        $uniqueId = $helper->generateUniqueId($review->getTitle(), $review->getDetail(), $product->getSku());
        $review->setUniqueId($uniqueId);
    }

    public function checkIfModuleEnabled($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        $helper->checkIfModuleEnabled(
            $moduleContainer,
            MageWorkshop_ImportExportReview_Helper_Data::IMPORTEXPORT_MODULE_NAME,
            MageWorkshop_ImportExportReview_Helper_Data::IMPORTEXPORT_XML_PATH_MODULE_ENABLE
        );
    }

    public function enableModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->enableModule(
            $moduleConfig,
            MageWorkshop_ImportExportReview_Helper_Data::IMPORTEXPORT_MODULE_NAME,
            MageWorkshop_ImportExportReview_Helper_Data::IMPORTEXPORT_XML_PATH_MODULE_ENABLE
        );
    }

    public function uninstallModule($observer)
    {
        /** @var MageWorkshop_Core_Helper_ModuleManager $helper */
        $helper = Mage::helper('drcore/moduleManager');
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        $helper->uninstallModule(
            $moduleConfig,
            MageWorkshop_ImportExportReview_Helper_Data::IMPORTEXPORT_MODULE_NAME,
            MageWorkshop_ImportExportReview_Helper_Data::IMPORTEXPORT_PACKAGE_FILE,
            MageWorkshop_ImportExportReview_Helper_Data::IMPORTEXPORT_UNINSTALL_PATH
        );
    }
}