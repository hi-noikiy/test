<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$setup->addAttributeGroup('catalog_product', 'Default', 'Alternative Titles', 13);

$installer->addAttribute('catalog_product', 'upsell_alt_title', array(
    'group' => 'Alternative Titles',
    'input' => 'text',
    'type' => 'varchar',
    'label' => 'Upsell - Alternative Title',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 1,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 0,
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => 0,
    'sort_order' => '20',
    'note' => 'Alternative title to be used. If left blank, default product title will be used.'
));

$attribute = $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'in_the_box_extra_alt_title');
$installer->addAttributeToGroup(
    Mage_Catalog_Model_Product::ENTITY, //catalog_product
    $installer->getDefaultAttributeSetId(Mage_Catalog_Model_Product::ENTITY), //Attribute Set Id
    'Alternative Titles', //Group Name
    $attribute['attribute_id'], //attribute id
    10//sort order
);

$installer->endSetup();