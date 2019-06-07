<?php

class MageWorkshop_ImportExportReview_Model_System_Config_Backend_Sync_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH  = 'crontab/jobs/drie_sync/schedule/cron_expr';

    /**
     * Cron settings after save
     *
     * @return true
     */
    protected function _afterSave()
    {
        $enabled   = $this->getData('groups/sync_settings/fields/enable/value');
        $testMode  = $this->getData('groups/sync_settings/fields/sync_cron_test/value');
        $time      = $this->getData('groups/sync_settings/fields/sync_cron/value');
        $frequency = $this->getData('groups/sync_settings/fields/sync_cron_test_frequency/value');

        if ($enabled) {
            if ($testMode) {
                $cronExprArray = array(
                    $frequency,           # Minute
                    '*',                  # Hour
                    '*',                  # Day of the Month
                    '*',                  # Month of the Year
                    '*',                  # Day of the Week
                );
            } else {
                $cronExprArray = array(
                    intval($time[1]),     # Minute
                    intval($time[0]),     # Hour
                    '*',                  # Day of the Month
                    '*',                  # Month of the Year
                    '*',                  # Day of the Week
                );
            }
            $cronExprString = join(' ', $cronExprArray);
        }
        else {
            $cronExprString = '';
        }

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('adminhtml')->__('Unable to save the cron expression.'));
        }
    }
}