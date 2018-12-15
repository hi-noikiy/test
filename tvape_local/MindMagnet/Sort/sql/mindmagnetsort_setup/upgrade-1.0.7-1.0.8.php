<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'set_value', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'set_value', 'frontend_input', 'price');

$installer->endSetup();

