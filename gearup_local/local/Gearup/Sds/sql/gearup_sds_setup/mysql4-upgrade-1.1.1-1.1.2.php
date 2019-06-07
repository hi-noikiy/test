<?php
$installer = $this;
$installer->startSetup();

$installer->removeAttribute('catalog_category', 'description_custom');
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'description_custom',  array(
    'group'         => 'General Information',
    'input'         => 'textarea',
    'type'          => 'text',
    'label'         => 'Description 2',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'wysiwyg_enabled' => true,
    'visible_on_front' => true,
    'is_html_allowed_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'description_custom',
    '3' /* Refers to the sort order of fields - see `eav_entity_attribute` for reference on the location of other fields.  I want this right below the active field, so 2 works for me.*/
);
$installer->endSetup();