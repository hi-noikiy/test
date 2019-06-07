<?php
$installer = $this;
$installer->startSetup();

$installer->removeAttribute('catalog_category', 'category_manufacturer_activity');
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'category_manufacturer_activity',  array(
    'type'     => 'int',
    'label'    => 'Preferred Manufacturer',
    'input'    => 'select',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => TRUE,
    'required'          => FALSE,
    'user_defined'      => TRUE,
    'default'           => "",
    'visible_on_front'  => TRUE,
    'used_in_product_listing'  => true,
    'source'   => 'gearup_activity/attribute_source_manufacturer',
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'category_manufacturer_activity',
    '99'
);

$installer->endSetup();