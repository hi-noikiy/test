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
    $mappingProfileTable = $installer->getTable('review_import_export_rating_mapping');
    if ($connection->isTableExists($mappingProfileTable) != true) {
        $mappingTable = $connection
            ->newTable($mappingProfileTable)
            ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'primary' => true,
                'identity' => true,
                'nullable' => false,
            ), 'Entity Id')
            ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'nullable' => false,
            ), 'Profile Id')
            ->addColumn('rating_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                'nullable' => false,
            ), 'Rating Id')
            ->addColumn('mapping_value', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable' => false,
            ), 'Mapping Value')
            ->setComment('Rating Import Export Profiles Table')
            ->addForeignKey($installer->getFkName('mageworkshop_importexportreview/ratingMapping', 'rating_id', 'rating/rating', 'rating_id'),
                'rating_id', $installer->getTable('rating/rating'), 'rating_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->addForeignKey($installer->getFkName('mageworkshop_importexportreview/ratingMapping', 'profile_id', 'mageworkshop_importexportreview/profile', 'id'),
                'profile_id', $installer->getTable('mageworkshop_importexportreview/profile'), 'id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
        $connection->createTable($mappingTable);
    }

} catch (Exception $e) {
    throw $e;
}

$installer->endSetup();
