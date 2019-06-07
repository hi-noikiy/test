<?php
/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Attributes for ElasticSearch engine
 */

/**
 * Counter of sales - filled by data from am_sorting_bestsellers
 */
if ($installer->getAttribute('catalog_product', 'sales_count')) {
    $installer->removeAttribute('catalog_product', 'sales_count');
}
$installer->addAttribute('catalog_product', 'sales_count', array(
    'group'             => 'general',
    'backend'           => '',
    'type'              => 'int',
    'table'             => '',
    'frontend'          => '',
    'input'             => 'text',
    'label'             => 'Bestsellers',
    'frontend_class'    => '',
    'source'            => '',
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'unique'            => false,
    'note'              => '',
    'visible'           => true,
    'sort_order'        => 1000,
    'global'            => true,
    'class'             => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false
));

/**
 * Flag to set value by force instead from table
 */
if ($installer->getAttribute('catalog_product', 'sales_count_force')) {
    $installer->removeAttribute('catalog_product', 'sales_count_force');
}
$installer->addAttribute('catalog_product', 'sales_count_force', array(
    'group'             => 'general',
    'backend'           => '',
    'type'              => 'int',
    'table'             => '',
    'frontend'          => '',
    'input'             => 'select',
    'label'             => 'Force Set Sales Count',
    'frontend_class'    => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'unique'            => false,
    'note'              => '',
    'visible'           => true,
    'sort_order'        => 1000,
    'global'            => true,
    'class'             => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false
));

/**
 * Counter of views - filled by data from am_sorting_most_viewed
 */
if ($installer->getAttribute('catalog_product', 'view_count')) {
    $installer->removeAttribute('catalog_product', 'view_count');
}
$installer->addAttribute('catalog_product', 'view_count', array(
    'group'             => 'general',
    'backend'           => '',
    'type'              => 'int',
    'table'             => '',
    'frontend'          => '',
    'input'             => 'text',
    'label'             => 'Most viewed',
    'frontend_class'    => '',
    'source'            => '',
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'unique'            => false,
    'note'              => '',
    'visible'           => true,
    'sort_order'        => 1000,
    'global'            => true,
    'class'             => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false
));

/**
 * Flag to set value by force instead from table
 */
if ($installer->getAttribute('catalog_product', 'view_count_force')) {
    $installer->removeAttribute('catalog_product', 'view_count_force');
}
$installer->addAttribute('catalog_product', 'view_count_force', array(
    'group'             => 'general',
    'backend'           => '',
    'type'              => 'int',
    'table'             => '',
    'frontend'          => '',
    'input'             => 'select',
    'label'             => 'Force Set View Count',
    'frontend_class'    => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'unique'            => false,
    'note'              => '',
    'visible'           => true,
    'sort_order'        => 1000,
    'global'            => true,
    'class'             => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false
));

/**
 * Counter of rating - filled by data from am_sorting_wished
 */
if ($installer->getAttribute('catalog_product', 'rate_count')) {
    $installer->removeAttribute('catalog_product', 'rate_count');
}
$installer->addAttribute('catalog_product', 'rate_count', array(
    'group'             => 'general',
    'backend'           => '',
    'type'              => 'int',
    'table'             => '',
    'frontend'          => '',
    'input'             => 'text',
    'label'             => 'Top Rated',
    'frontend_class'    => '',
    'source'            => '',
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'unique'            => false,
    'note'              => '',
    'visible'           => true,
    'sort_order'        => 1000,
    'global'            => true,
    'class'             => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false
));

/**
 * Flag to set value by force instead from table
 */
if ($installer->getAttribute('catalog_product', 'rate_count_force')) {
    $installer->removeAttribute('catalog_product', 'rate_count_force');
}
$installer->addAttribute('catalog_product', 'rate_count_force', array(
    'group'             => 'general',
    'backend'           => '',
    'type'              => 'int',
    'table'             => '',
    'frontend'          => '',
    'input'             => 'select',
    'label'             => 'Force Set Rate Count',
    'frontend_class'    => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'unique'            => false,
    'note'              => '',
    'visible'           => true,
    'sort_order'        => 1000,
    'global'            => true,
    'class'             => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'apply_to'          => '',
    'is_configurable'   => false
));

$installer->endSetup();