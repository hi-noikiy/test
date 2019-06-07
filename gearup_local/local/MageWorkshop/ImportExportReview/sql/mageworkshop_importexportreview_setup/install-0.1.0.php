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
    $drieSyncStoresTable = $installer->getTable('review_sync_store');
    if ($connection->isTableExists($drieSyncStoresTable) != true) {
        $syncTable = $connection
            ->newTable($drieSyncStoresTable)
            ->addColumn('id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'primary'  => true,
                'identity' => true,
                'nullable'  => false,
            ), 'Sync Id')
            ->addColumn('store_identity', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable'  => false,
            ), 'Store Identity')
            ->addColumn('store_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable'  => false,
            ), 'Store URL')
            ->addColumn('store_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable'  => false,
            ), 'Store Identity')
            ->addColumn('api_username', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable'  => false,
            ), 'Store Api Username')
            ->addColumn('api_key', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable'  => false,
            ), 'Store Api Key')
            ->addColumn('last_exported_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable'  => true,
            ), 'Last ReviewId')
            ->addColumn('last_failed_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable'  => true,
            ), 'Last Failed ReviewId')
            ->addColumn('fails_count', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable'  => true,
            ), 'Fails Count')
            ->addColumn('last_import', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => true,
            ), 'Last Import Date')
            ->setComment('Review Stores Sync Table');

        $connection->createTable($syncTable);
    }

} catch (Exception $e) {
    throw $e;
}

$installer->endSetup();
