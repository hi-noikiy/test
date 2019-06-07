<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */$installer = $this;

if ($installer->getConnection()->showTableStatus($installer->getTable('enterprise_cms_page_revision'))) {
    $installer->getConnection()->addColumn($installer->getTable('enterprise_cms_page_revision'), 'meta_title', "varchar(255) NOT NULL DEFAULT ''");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('cms_page'), 'meta_title')) {
    $installer->getConnection()->addColumn($installer->getTable('cms_page'), 'meta_title', "varchar(255) NOT NULL DEFAULT ''");
}

if (!$installer->getConnection()->tableColumnExists($installer->getTable('catalog/eav_attribute'), 'layered_navigation_canonical')) {
    $installer->getConnection()->addColumn($installer->getTable('catalog/eav_attribute'), 'layered_navigation_canonical', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\'');
}

$installer->startSetup();

$installer->addAttribute('catalog_product', 'canonical_url',
    array(
    'group'            => 'Meta Information',
    'type'             => 'text',
    'backend'          => 'seosuite/catalog_product_attribute_backend_meta_canonical',
    'frontend'         => '',
    'label'            => 'Canonical URL',
    'input'            => 'select',
    'class'            => '',
    'source'           => 'seosuite/catalog_product_attribute_source_meta_canonical',
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'          => true,
    'required'         => false,
    'user_defined'     => false,
    'default'          => '',
    'searchable'       => false,
    'filterable'       => false,
    'comparable'       => false,
    'visible_on_front' => false,
    'unique'           => false,
    'sort_order'       => 60
));

$installer->addAttribute('catalog_product', 'canonical_cross_domain', array(
    'group'             => 'Meta Information',
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Cross Domain Canonical URL',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'seosuite/system_config_source_crossdomain',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '0',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'sort_order'        => 50
));

$installer->addAttribute('catalog_product', 'meta_robots', array(
    'group'            => 'Meta Information',
    'type'             => 'text',
    'backend'          => '',
    'frontend'         => '',
    'label'            => 'Meta Robots',
    'input'            => 'select',
    'class'            => '',
    'source'           => 'seosuite/catalog_product_attribute_source_meta_robots',
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'          => true,
    'required'         => false,
    'user_defined'     => false,
    'default'          => '',
    'searchable'       => false,
    'filterable'       => false,
    'comparable'       => false,
    'visible_on_front' => false,
    'unique'           => false,
    'sort_order'        => 60
));

$installer->addAttribute('catalog_category', 'meta_robots', array(
    'group'             => 'General Information',
    'type'              => 'text',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Meta Robots',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'seosuite/catalog_product_attribute_source_meta_robots',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'sort_order'        => 60
));


$installer->endSetup();

?>