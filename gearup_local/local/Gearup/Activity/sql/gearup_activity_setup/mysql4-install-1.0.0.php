<?php
$installer = $this;
$installer->startSetup();

$installer->removeAttribute('catalog_category', 'category_sell_activity');
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'category_sell_activity',  array(
    'type'     => 'text',
    'label'    => 'Preferred Product Ordered',
    'input'    => 'text',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => TRUE,
    'required'          => FALSE,
    'user_defined'      => TRUE,
    'default'           => "",
    'visible_on_front'  => TRUE,
    'used_in_product_listing'  => true,
    'note' => 'Mutiple Product Sku seperate by comma(,)',
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'category_sell_activity',
    '100'
);

$installer->endSetup();