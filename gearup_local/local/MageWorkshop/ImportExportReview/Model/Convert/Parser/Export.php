<?php

class MageWorkshop_ImportExportReview_Model_Convert_Parser_Export extends Mage_Eav_Model_Convert_Parser_Abstract
{
    public function parse() {}

    public function unparse()
    {
        /** @var Mage_Review_Model_Resource_Review_Collection $reviewsCollection */
        $reviewsCollection = Mage::getModel('review/review')->getResourceCollection()->setDateOrder();
        $storeId           = $this->getVar('store');
        $fullImagesPath    = $this->getVar('full_images_path');
        $filterReviewIds   = $this->getVar('sync_review_ids');

        if ($storeId != 0) {
            $reviewsCollection->addStoreFilter($storeId);
        }

        if ($filterReviewIds != 0) {
            $reviewsCollection->addFieldToFilter('main_table.review_id', array('in' => explode(',', $filterReviewIds)));
        }

        $reviewsCollection->addRateVotes();

        /** @var MageWorkshop_ImportExportReview_Helper_ImportExport $drieHelper */
        $drieHelper = Mage::helper('mageworkshop_importexportreview/importExport');
        $reviews = $drieHelper->exportReviews($reviewsCollection, $fullImagesPath);

        /** @var Mage_Review_Model_Review|MageWorkshop_DetailedReview_Model_Review $review */
        foreach($reviews as $exportFields) {
            $this->getBatchExportModel()
                ->setId(null)
                ->setBatchId($this->getBatchModel()->getId())
                ->setBatchData($exportFields)
                ->setStatus(1)
                ->save();
        }

        return $this;
    }
}