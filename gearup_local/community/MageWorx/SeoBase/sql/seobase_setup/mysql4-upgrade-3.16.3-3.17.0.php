<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

$installer = $this;

if (!$installer->getConnection()->tableColumnExists($installer->getTable('cms_page'), 'meta_robots')) {
    $installer->getConnection()->addColumn($installer->getTable('cms_page'), 'meta_robots', 'varchar(255) NOT NULL');
}

$installer->addAttribute('catalog_product', 'canonical_url',
    array(
    'group'            => 'Meta Information',
    'type'             => 'text',
    'backend'          => '',
    'frontend'         => '',
    'label'            => 'Canonical URL',
    'input'            => 'select',
    'class'            => '',
    'source'           => '',
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
    'source'            => '',
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
    'source'           => '',
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
    'source'            => '',
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