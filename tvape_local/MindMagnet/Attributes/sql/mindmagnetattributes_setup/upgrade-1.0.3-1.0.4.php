<?php
$installer = $this;

$installer->startSetup();

$setId = $installer->getDefaultAttributeSetId('catalog_product');

$groupId = $installer->getAttributeGroupId($installer->getEntityTypeId('catalog_product'), $setId, 'Default');

$installer->addAttributeToGroup('catalog_product', $setId, $groupId, 'manufacturer', 10);

$installer->endSetup();