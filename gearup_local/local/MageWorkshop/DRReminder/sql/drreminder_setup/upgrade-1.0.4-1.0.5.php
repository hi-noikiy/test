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

$drReviewRemindersTable = $installer->getTable('drreminder/review_reminders');

$installer->run("
            alter table {$drReviewRemindersTable} drop foreign key FK_REVIEW_REMINDERS_EMAIL_CUSTOMER_ENTITY_EMAIL;
");

$installer->endSetup();