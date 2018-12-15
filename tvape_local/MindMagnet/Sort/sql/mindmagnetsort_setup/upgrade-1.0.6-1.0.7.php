<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'vapor_quality', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'vapor_quality', 'frontend_input', 'price');

$installer->updateAttribute('catalog_product', 'manufacturing_quality', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'vapor_quality', 'frontend_input', 'price');

$installer->updateAttribute('catalog_product', 'temperature_quality', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'temperature_quality', 'frontend_input', 'price');

$installer->updateAttribute('catalog_product', 'portability', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'portability', 'frontend_input', 'price');

$installer->updateAttribute('catalog_product', 'discreetness', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'discreetness', 'frontend_input', 'price');

$installer->updateAttribute('catalog_product', 'convenience', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'convenience', 'frontend_input', 'price');

$installer->updateAttribute('catalog_product', 'battery_life', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'battery_life', 'frontend_input', 'price');

$installer->updateAttribute('catalog_product', 'overall_score', 'backend_type', 'decimal');
$installer->updateAttribute('catalog_product', 'overall_score', 'frontend_input', 'price');

$installer->endSetup();

