<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'mageworkshop_detailedreview_complaint_type'
 */
$connection = $installer->getConnection();

$complaintTypeTable = $installer->getTable('detailedreview/complaint_type');

if (!$connection->isTableExists($complaintTypeTable)) {
    $table = $connection
        ->newTable($complaintTypeTable)
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 20, array(
            'nullable' => false,
            'primary'  => true,
            'identity' => true
        ), 'Entity Id')
        ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array('nullable' => false), 'Complaint Title')
        ->addColumn('status_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
            'unsigned' => true,
            'nullable' => false,
            'default'  => '1'
        ), 'Status code');

    $connection->createTable($table);
}

/**
 * Create table 'mageworkshop_detailedreview_review_customer_complaint'
 */
$reviewCustomerComplaintTable = $installer->getTable('detailedreview/review_customer_complaint');

$connection = $installer->getConnection();

if (!$connection->isTableExists($reviewCustomerComplaintTable)) {
    $table = $connection
        ->newTable($reviewCustomerComplaintTable)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
            'nullable' => false,
            'primary'  => true,
            'identity' => true
        ), 'Entity Id')
        ->addColumn('review_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array('unsigned' => true), 'Review id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('unsigned' => true), 'Customer ID')
        ->addColumn('complaint_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('unsigned' => false), 'Complaint Id')
        ->addForeignKey(
            $installer->getFkName(
                'detailedreview/review_customer_complaint',
                'review_id',
                'review/review',
                'review_id'
            ),
            'review_id',
            $installer->getTable('review/review'),
            'review_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $installer->getFkName(
                'detailedreview/review_customer_complaint',
                'customer_id',
                'customer/entity',
                'entity_id'
            ),
            'customer_id',
            $installer->getTable('customer/entity'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $installer->getFkName(
                'detailedreview/review_customer_complaint',
                'complaint_id',
                'detailedreview/complaint_type',
                'entity_id'
            ),
            'complaint_id',
            $installer->getTable('detailedreview/complaint_type'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        );

    $connection->createTable($table);
}

// Change review_helpful table configuration for changing  from MyISAM to InnoDB engine

/** @var MageWorkshop_DetailedReview_Model_Mysql4_Review $drResourceModel */
$drResourceModel = Mage::getResourceModel('detailedreview/review');

$drReviewHelpfulTable = $installer->getTable('detailedreview/review_helpful');
$customerEntityTable  = $installer->getTable('customer/entity');

if ($drResourceModel->isMyIsamEngine($drReviewHelpfulTable)) {
    $reviewTableName = $installer->getTable('review/review');
    $drResourceModel->removeTableData($drReviewHelpfulTable, 'review_id', $reviewTableName, 'review_id');

    $installer->run("
        ALTER TABLE {$drReviewHelpfulTable}
            CHANGE COLUMN `review_id` `review_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Review Id';

        ALTER TABLE {$drReviewHelpfulTable} ENGINE=InnoDB;

        ALTER TABLE {$drReviewHelpfulTable}
            ADD CONSTRAINT `FK_REVIEW_HELPFUL_REVIEW_ID_REVIEW_REVIEW_ID`
            FOREIGN KEY (`review_id`)
            REFERENCES {$reviewTableName} (`review_id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE;
    ");
}

// Change review_author_ips table configuration for changing  from MyISAM to InnoDB engine

$drAuthorIpsTable = $installer->getTable('detailedreview/author_ips');

if ($drResourceModel->isMyIsamEngine($drAuthorIpsTable)) {
    $drResourceModel->removeTableData($drAuthorIpsTable, 'customer_id', $customerEntityTable, 'entity_id');

    $installer->run("
        ALTER TABLE {$drAuthorIpsTable} 
            CHANGE COLUMN `customer_id` `customer_id` INT(10) UNSIGNED NOT NULL COMMENT 'Customer ID';        
        
        ALTER TABLE {$drAuthorIpsTable} ENGINE=InnoDB;
        
        ALTER TABLE {$drAuthorIpsTable} 
            ADD CONSTRAINT `FK_REVIEW_AUTHOR_IPS_CUSTOMER_ID_CUSTOMER_ENTITY_ENTITY_ID`
            FOREIGN KEY (`customer_id`)
            REFERENCES {$customerEntityTable} (`entity_id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE;              
    ");
}

Mage::getConfig()->saveConfig('detailedreview/datetime_options/enable_to_set_timezone', 1);

$coreConfigTable = $installer->getTable('core/config_data');

$configDateFormats = $installer->getConnection()->fetchAll("
    SELECT 
        * 
    FROM 
        {$coreConfigTable} 
    WHERE 
        path = 'detailedreview/datetime_options/date_format'
");

$configTimeFormats = $installer->getConnection()->fetchAll("
    SELECT 
        * 
    FROM 
        {$coreConfigTable} 
    WHERE 
        path = 'detailedreview/datetime_options/time_format'
");

if ($configDateFormats) {
    foreach ($configDateFormats as $dateFormat) {
        if (isset($dateFormat['config_id']) && !isset($dateFormat['value'])) {
            Mage::getConfig()->saveConfig(
                'detailedreview/datetime_options/date_format',
                'DD/MM/YYYY',
                $dateFormat['scope'],
                $dateFormat['scope_id']
            );
        }
    }
} else {
    Mage::getConfig()->saveConfig('detailedreview/datetime_options/date_format', 'DD/MM/YYYY');
}

if ($configTimeFormats) {
    foreach ($configTimeFormats as $timeFormat) {
        if (isset($timeFormat['config_id']) && !isset($timeFormat['value'])) {
            Mage::getConfig()->saveConfig(
                'detailedreview/datetime_options/time_format',
                'HH:mm',
                $timeFormat['scope'],
                $timeFormat['scope_id']
            );
        }
    }
} else {
    Mage::getConfig()->saveConfig('detailedreview/datetime_options/time_format', 'HH:mm');
}

$installer->endSetup();