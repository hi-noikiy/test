<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Change review_reminders table configuration for changing  from MyISAM to InnoDB engine

/** @var MageWorkshop_DetailedReview_Model_Mysql4_Review $drResourceModel */
$drResourceModel = Mage::getResourceModel('detailedreview/review');

$drReviewRemindersTable = $installer->getTable('drreminder/review_reminders');

if ($drResourceModel->isMyIsamEngine($drReviewRemindersTable)) {
    $customerEntityTable = $installer->getTable('customer/entity');
    $drResourceModel->removeTableData($drReviewRemindersTable, 'customer_id', $customerEntityTable, 'entity_id');

    $salesFlatOrderTable = $installer->getTable('sales/order');
    $drResourceModel->removeTableData($drReviewRemindersTable, 'order_id', $salesFlatOrderTable, 'entity_id');

    $installer->run("
        ALTER TABLE {$drReviewRemindersTable}
            CHANGE COLUMN `email` `email` VARCHAR(255) NULL COMMENT 'Email';
            
        ALTER TABLE {$drReviewRemindersTable}
            CHANGE COLUMN `order_id` `order_id` INT(10) UNSIGNED NOT NULL COMMENT 'Order ID';

        ALTER TABLE {$drReviewRemindersTable} ENGINE=InnoDB;

        ALTER TABLE {$drReviewRemindersTable}
            ADD CONSTRAINT `FK_REVIEW_REMINDERS_EMAIL_CUSTOMER_ENTITY_EMAIL`
            FOREIGN KEY (`email`)
            REFERENCES {$customerEntityTable} (`email`)
                ON DELETE CASCADE
                ON UPDATE CASCADE;
                
        ALTER TABLE {$drReviewRemindersTable}
            ADD CONSTRAINT `FK_REVIEW_REMINDERS_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID`
            FOREIGN KEY (`order_id`)
            REFERENCES {$salesFlatOrderTable} (`entity_id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE;                
    ");
}

$installer->endSetup();