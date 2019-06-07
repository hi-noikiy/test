<?php

$installer = $this;
$installer->startSetup();

$installer->addAttribute(
    'catalog_product',
    'discontinued_category',
    array(
        'label'                         => 'Discontinued Category',
        'required'                      => false,
        'input'                         => 'select',
        'source'                        => 'discontinuedproducts/entity_source',
        'global'                        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'default'                       => 'none',
        'visible_on_front'              => 1,
        'position'                      => 1,
        'group'                         => 'General',
        'sort_order'                    => 22,
    )
);

$installer->addAttribute(
    'catalog_product',
    'discontinued_product',
    array(
        'label'                         => 'Discontinued Product',
        'type'                          => 'int',
        'input'                         => 'select',
        'global'                        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'                       => 1,
        'required'                      => 1,
        'visible_on_front'              => 0,
        'is_html_allowed_on_front'      => 0,
        'is_configurable'               => 0,
        'source'                        => 'eav/entity_attribute_source_boolean',
        'searchable'                    => 1,
        'filterable'                    => 0,
        'default'                       => 0,
        'comparable'                    => 1,
        'unique'                        => false,
        'user_defined'                  => false,
        'is_user_defined'               => false,
        'used_in_product_listing'       => true,
        'position'                      => 1,
        'group'                         => 'General',
        'sort_order'                    => 21,
    )
);

$installer->endSetup();
