<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$setup->addAttributeGroup('catalog_product', 'Default', 'In the box', 12);

$installer->addAttribute('catalog_product', 'in_the_box_extra_title', array(
    'group' => 'In the box',
    'input' => 'text',
    'type' => 'varchar',
    'label' => 'In the box - Titles',
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
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'sort_order' => '20',
    'note' => 'Use , separator for titles. Example: title1, title2, title3'
));


$installer->endSetup();