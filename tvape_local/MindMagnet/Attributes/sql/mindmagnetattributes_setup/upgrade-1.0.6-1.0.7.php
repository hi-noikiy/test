<?php
$installer = $this;

$installer->startSetup();

$this->addAttribute('catalog_product', 'webrotate_path', array(
    'group' => 'General',
    'input' => 'text',
    'type'  => 'varchar',
    'label' => '360 Viewer Path',
    'required' => 0,
    'user_defined' => 1,
    'unique' => 0,
    'global' => 0,
    'visible' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'visible_on_front' => 0,
    'html_allowed_on_front' => 1,
    'used_for_price_rules' => 0,
    'filterable_in_search' => 0,
    'used_in_product_listing' => 0,
    'used_for_sort_by' => 0,
    'configurable' => 0,
    'visible_in_advanced_search' => 0,
    'position' => 0,
    'wysiwyg_enabled' => 1,
    'used_for_promo_rules' => 0
));

$installer->endSetup();