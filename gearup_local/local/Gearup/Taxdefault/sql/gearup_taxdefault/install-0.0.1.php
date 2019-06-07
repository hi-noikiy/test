<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();

// Will update the attribute
$installer->updateAttribute('catalog_product','tax_class_id','default_value',2);

$installer->endSetup();