<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = new Mage_Core_Model_Resource_Setup();
$installer->startSetup();
$installer->getConnection()
->addColumn($installer->getTable('amfeed/profile'),'price_rounding', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => false,
    'length'    => 255,
    'after'     => null, // column name to insert new column after
    'comment'   => 'Price Rounding'
    ));
$this->endSetup();