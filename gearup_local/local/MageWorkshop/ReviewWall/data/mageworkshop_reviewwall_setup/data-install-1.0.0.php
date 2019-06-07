<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ReviewWall
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$connection->beginTransaction();

try {
    if ($connection->isTableExists($installer->getTable('admin/permission_block')) == true) {
        $connection->insertMultiple(
            $installer->getTable('admin/permission_block'),
            array(array('block_name' => 'reviewwall/widget_wall', 'is_allowed' => 1))
        );
    }

    $connection->commit();

} catch (Exception $e) {
    Mage::logException($e);
    $connection->rollBack();
}

$installer->endSetup();