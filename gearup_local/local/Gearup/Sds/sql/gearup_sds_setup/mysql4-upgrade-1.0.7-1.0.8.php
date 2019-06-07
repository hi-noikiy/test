<?php
$installer = $this;
$installer->startSetup();
/**
 * This is an entity associated with catalog_category
 * @var  integer
 */
$installer->removeAttribute('catalog_category', 'category_layer_category_filter');
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
/**
 * Use the default attribute set for catalog_category -- this refers to the table `eav_attribute_set`
 * In my case, it's 3, but this function should automatically get that for you.
 * @var integer
 */
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
/**
 * This determines what group (tab) that the field will be placed. "General Information"
 * is the default tab, and I'm okay with this, but see `eav_attribute_group` for other
 * groups
 * @var integer
 */
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);


$installer->addAttribute('catalog_category', 'show_category_filter',  array(
    'type'     => 'int', /* Type - see eav_entity_* for the different types */
    'label'    => 'Show category filter', /* Your label */
    'input'    => 'select', /* This refers to the type of form field should display*/
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => TRUE,
    'required'          => FALSE,
    'user_defined'      => FALSE,
    'default'           => 0,
    'visible_on_front'  => TRUE,
    'source' => 'eav/entity_attribute_source_boolean',
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'show_category_filter',
    '110' /* Refers to the sort order of fields - see `eav_entity_attribute` for reference on the location of other fields.  I want this right below the active field, so 2 works for me.*/
);

$installer->endSetup();