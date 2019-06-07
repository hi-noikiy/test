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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

try {
    $drieSyncHistoryTable = $installer->getTable('review_sync_history');

    if ($connection->isTableExists($drieSyncHistoryTable) != true) {
        $table = $connection
            ->newTable($drieSyncHistoryTable)
            ->addColumn('id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'primary'  => true,
                'identity' => true,
                'nullable'  => false,
            ), 'History Id')
            ->addColumn('sync_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'nullable'  => false,
            ), 'Store Sync Id')
            ->addColumn('type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable'  => false,
            ), 'Store Sync Type')
            ->addColumn('last_export', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => false,
            ), 'Last Export Date')
            ->addColumn('reviews_count', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'nullable'  => false,
            ), 'Imported Reviews')
            ->addForeignKey($installer->getFkName('mageworkshop_importexportreview/history', 'sync_id', 'mageworkshop_importexportreview/sync', 'id'),
                'sync_id', $installer->getTable('mageworkshop_importexportreview/sync'), 'id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->setComment('Review Sync History Table');

        $connection->createTable($table);
    }

} catch (Exception $e) {
    throw $e;
}

$installer->endSetup();
