<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'in_the_box_extra_alt_title', array(
    'group' => 'In the box',
    'input' => 'text',
    'type' => 'varchar',
    'label' => 'In the box - Alternative Title',
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


$installer->endSetup();