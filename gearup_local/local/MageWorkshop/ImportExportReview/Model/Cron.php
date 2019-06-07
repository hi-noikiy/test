<?php

class MageWorkshop_ImportExportReview_Model_Cron
{
    /**
     * cron job - drie_sync_reviews
     */
    public function syncReviews()
    {
        if (Mage::getStoreConfigFlag('drie/sync_settings/enable')) {
            /** @var MageWorkshop_ImportExportReview_Model_Sync $syncStore */
            $syncStore = Mage::getModel('mageworkshop_importexportreview/sync');
            try {
                $syncStore->syncReviews();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }
}