<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_product', 'associated_upgrade_sku', array(
    'type'              => 'varchar',
    'label'             => 'Associated Upgrade SKU',
    'input'             => 'text',
    'backend'           => '',
    'frontend'          => '',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'used_in_product_listing' => false,
    'default'           => NULL,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'group'             => 'general'
));
$installer->endSetup();