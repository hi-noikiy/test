<?php
/** @var Mage_Eav_Model_Entity_Setup $this*/
$installer = $this;


$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'versions', 'frontend_input', 'textarea');
$installer->updateAttribute('catalog_product', 'materials', 'frontend_input', 'textarea');
$installer->updateAttribute('catalog_product', 'key_features', 'frontend_input', 'textarea');

$installer->endSetup();

