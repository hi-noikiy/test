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
    $drieProfileTable = $installer->getTable('review_import_export_profile');

    if ($connection->isTableExists($drieProfileTable) != true) {
        $table = $connection
            ->newTable($drieProfileTable)
            ->addColumn('id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'primary'  => true,
                'identity' => true,
                'nullable'  => false,
            ), 'Profile Id')
            ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable'  => false,
            ), 'Profile Name')
            ->addColumn('type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable'  => false,
            ), 'Profile Type')
            ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
                'nullable'  => false,
            ), 'Store Id')
            ->addColumn('use_full_image_path', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
                'nullable'  => false,
            ), 'Use Full Rview Images Path')
            ->addColumn('max_width', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
                'nullable'  => true,
            ), 'Resize image if it is more than')
            ->addColumn('max_height', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
                'nullable'  => true,
            ), 'Resize image if it is more than')
            ->addColumn('create_rating', Varien_Db_Ddl_Table::TYPE_SMALLINT, 2, array(
                'nullable'  => false,
            ), 'Crete Rating')
            ->addColumn('rating_mapping', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
                'nullable'  => true,
            ), 'Rating Mapping')
            ->addColumn('create_proscons', Varien_Db_Ddl_Table::TYPE_SMALLINT, 2, array(
                'nullable'  => false,
            ), 'Create Pros/Cons')
            ->addColumn('proscons_mapping', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
                'nullable'  => true,
            ), 'Pros/Cons Mapping')
            ->setComment('Review Import Export Profiles Table');

        $connection->createTable($table);
    }
} catch (Exception $e) {
    throw $e;
}

$installer->endSetup();
