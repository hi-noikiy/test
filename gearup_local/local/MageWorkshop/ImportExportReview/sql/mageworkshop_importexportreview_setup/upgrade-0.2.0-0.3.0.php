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

$connection->addColumn($installer->getTable('review/review'), 'unique_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'comment'  => 'Global unique identifier'
));

$installer->run("
    UPDATE {$installer->getTable('review/review')} `r` 
    LEFT join {$installer->getTable('review/review_detail')} `rd` 
    ON r.review_id = rd.review_id 
    LEFT join {$installer->getTable('catalog/product')} `cpe`
    ON cpe.entity_id = r.entity_pk_value
    SET r.unique_id = md5(concat(trim(rd.title), trim(rd.detail), trim(cpe.sku))), r.created_at = r.created_at
    ");

$installer->endSetup();