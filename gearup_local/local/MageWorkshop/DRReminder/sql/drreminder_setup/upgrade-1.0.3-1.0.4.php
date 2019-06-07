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

$coreConfigTable = $installer->getTable('core/config_data');

$changeConfig = array(
    'drreminder/settings/remind_delay_period' => 3,
);

foreach ($changeConfig as $path => $value) {
    $installer->run("UPDATE `$coreConfigTable` SET `value` = '$value' WHERE `path` = '$path'");
}

$installer->endSetup();
