<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$setup->addAttributeGroup('catalog_product', 'Default', 'AddOn Popup', 12);

$installer->addAttribute('catalog_product', 'addonpoup_general_enabled', array(
    'group' => 'Cross-sells',
    'input' => 'select',
    'type' => 'int',
    'label' => 'Activate Popup',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 0,
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => 0,
    'sort_order' => '20',
    'default'   => '0',
    'note' => 'Enable/Disable AddOn Popup functionality'
));

$attribute = $installer->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'addonpoup_general_enabled');
$installer->addAttributeToGroup(
    Mage_Catalog_Model_Product::ENTITY, //catalog_product
    $installer->getDefaultAttributeSetId(Mage_Catalog_Model_Product::ENTITY), //Attribute Set Id
    'AddOn Popup', //Group Name
    $attribute['attribute_id'], //attribute id
    10
);

$installer->endSetup();