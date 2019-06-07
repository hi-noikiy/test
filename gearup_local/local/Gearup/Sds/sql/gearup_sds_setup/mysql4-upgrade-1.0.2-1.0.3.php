<?php

$installer = $this;
$installer->startSetup();

// Add new Attribute group
$groupName = 'Low Stock Threshold';
$entityTypeId = $installer->getEntityTypeId('catalog_product');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 20);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

// Add existing attribute to group
$attributeId = $installer->getAttributeId($entityTypeId, 'low_stock');
$installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);

$installer->endSetup();


