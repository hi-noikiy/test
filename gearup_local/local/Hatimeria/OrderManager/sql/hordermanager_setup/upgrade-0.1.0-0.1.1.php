<?php
/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->removeAttribute('catalog_product', 'is_special');
$installer->addAttribute('catalog_product', 'is_special', array(
    'group'             => '',
    'backend'           => '',
    'type'              => 'int',
    'table'             => '',
    'frontend'          => '',
    'input'             => 'select',
    'label'             => 'Same Day Shipping',
    'frontend_class'    => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'unique'            => false,
    'note'              => 'Special Shipment',
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