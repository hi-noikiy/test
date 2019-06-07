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

class MageWorkshop_ImportExportReview_Model_Uninstall extends Mage_Core_Model_Abstract
{

    public function clearDatabaseInformation()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

        $setup->startSetup();

        $coreResource = Mage::getSingleton('core/resource');

        $drieSyncStoresTable = $coreResource->getTableName('review_sync_store');
        $drieProfileTable = $coreResource->getTableName('review_import_export_profile');
        $reviewTable = $coreResource->getTableName('review/review');
        $mappingProfileTable = $coreResource->getTableName('review_import_export_rating_mapping');
        $drieSyncHistoryTable = $coreResource->getTableName('review_sync_history');

        $coreResourceTable = $coreResource->getTableName('core/resource');

        $sql  = "DROP TABLE IF EXISTS `$drieSyncStoresTable`;";
        $sql  .= "DROP TABLE IF EXISTS `$drieProfileTable`;";
        $sql  .= "DROP TABLE IF EXISTS `$mappingProfileTable`;";
        $sql  .= "DROP TABLE IF EXISTS `$drieSyncHistoryTable`;";

        $setup->run($sql);

        $coreResource->getConnection('core_write')->dropColumn($reviewTable, 'unique_id');

        try {
            $setup->deleteTableRow($coreResourceTable,'code','mageworkshop_importexportreview_setup');
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $setup->endSetup();
    }
}