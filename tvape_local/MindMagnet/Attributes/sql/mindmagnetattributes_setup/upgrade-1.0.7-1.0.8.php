<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$installer->startSetup();

$installer->updateAttribute('catalog_product', 'manufacturer','apply_to',  'simple,configurable');
$installer->updateAttribute('catalog_product', 'weight', 'apply_to' , 'simple,configurable');

$installer->endSetup();
