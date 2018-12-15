<?php

$installer = $this;

$installer->startSetup();

if(!$installer->getAttributeId('catalog_product', 'em_sendsms')){
$installer->addAttribute('catalog_product', 'em_sendsms', array(
                        'type'                       => 'text',
                        'label'                      => 'SMS TEXT - Discount code',
						'note'              		 => 'Maximum 255 chars',
                        'input'                      => 'textarea',
                        'required'                   => false,
                        'sort_order'                 => 4,
                        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'wysiwyg_enabled'            => true,
                        'is_html_allowed_on_front'   => true,
                        'group'                      => 'General',
                    ));
}
if(!$installer->getAttributeId('catalog_product', 'em_enablesms')){
$installer->addAttribute('catalog_product', 'em_enablesms', array(
    'group'             => 'General',
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Active discount code',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'simple,virtual',
    'is_configurable'   => false
));
}
$installer->endSetup(); 