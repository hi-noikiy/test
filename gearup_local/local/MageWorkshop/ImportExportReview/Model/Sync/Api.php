<?php

class MageWorkshop_ImportExportReview_Model_Sync_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieves review items based on product SKU list and last failed review ID (if some error appeared during the review save)
     *
     * @param string $storeIdentity
     * @param int $batchSize
     * @param string $skuList
     * @param null $lastFailedId
     * @return array
     * @throws Exception
     * @throws Mage_Api_Exception
     */
    public function items($storeIdentity, $batchSize, $skuList = '', $lastFailedId = null)
    {
        $reviewsData = array();
        $newLastId = 0;
        if ($code = $this->_validate($storeIdentity)) {
            $this->_fault($code);
        } else {
            /** @var MageWorkshop_ImportExportReview_Model_Sync $syncStore */
            $syncStore         = Mage::getModel('mageworkshop_importexportreview/sync')->load($storeIdentity);
            $lastExportedId = $syncStore->getLastExportedId();
            /** @var MageWorkshop_ImportExportReview_Model_History $syncHistory */
            $syncHistory       = Mage::getModel('mageworkshop_importexportreview/history');

            /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
            $productCollection = Mage::getModel('catalog/product')->getCollection();

            if ($skuList) {
                $skuList = explode(',', $skuList);
                $productCollection->addFieldToFilter('sku', array('in' => $skuList));
            }

            if ($productCollection->count()) {
                /** @var Mage_Review_Model_Resource_Review_Collection $reviewsCollection */
                $reviewsCollection = Mage::getModel('review/review')->getResourceCollection()->setDateOrder('ASC');
                $reviewsCollection->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED);
                if ($lastFailedId) {
                    $reviewsCollection->addFieldToFilter('main_table.review_id', array('gteq' => $lastFailedId));
                } elseif ($lastExportedId) {
                    $reviewsCollection->addFieldToFilter('main_table.review_id', array('gt' => $lastExportedId));
                }

                $reviewsCollection->setPageSize($batchSize)->addRateVotes();
                /** @var MageWorkshop_ImportExportReview_Helper_ImportExport $drieHelper */
                $drieHelper = Mage::helper('mageworkshop_importexportreview/importExport');
                $reviewsData = $drieHelper->exportReviews($reviewsCollection);

                if (count($reviewsData)) {
                    $newLastId = end($reviewsData);
                    $newLastId = $newLastId['entity_id'];
                }
            } else {
                throw new Exception('No products found');
            }
            
            if ($newLastId && !$skuList) {
                $syncStore->setData('last_exported_id', $newLastId);
                $syncStore->save();
            }

            $syncHistory->setData('last_export', date('Y-m-d H:i:s', time()));
            $syncHistory->setData('sync_id', $syncStore->getId());
            $syncHistory->setData('type', MageWorkshop_ImportExportReview_Model_History::TYPE_EXPORT);
            $syncHistory->setData('reviews_count', count($reviewsData));
            $syncHistory->save();
        }

        return $reviewsData;
    }

    /**
     * @param $storeIdentity
     * @return int
     */
    protected function _validate($storeIdentity)
    {
        /** @var MageWorkshop_ImportExportReview_Model_Sync $syncModel */
        $syncModel = Mage::getModel('mageworkshop_importexportreview/sync');
        $code = '';

        if (!$storeIdentity || !$syncModel->load($storeIdentity)->getId()) {
            $code = 'store_invalid';
        }

        return $code;
    }
}