<?php
$installer = $this;

$installer->startSetup();

$this->addAttribute('catalog_product', 'in_the_press', array(
    'group' => 'General',
    'input' => 'textarea',
    'type'  => 'text',
    'label' => 'In the press',
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

$this->addAttribute('catalog_product', 'how_to_videos', array(
    'group' => 'General',
    'input' => 'textarea',
    'type'  => 'text',
    'label' => 'How to videos',
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

$this->addAttribute('catalog_product', 'in_the_box_extra', array(
    'group' => 'General',
    'input' => 'textarea',
    'type'  => 'text',
    'label' => 'In the box - extra',
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

$this->addAttribute('catalog_product', 'compare_size', array(
    'group' => 'Images',
    'input' => 'media_image',
    'type' => 'varchar',
    'label' => 'Compare size image',
    'frontend' => 'catalog/product_attribute_frontend_image'
));

$this->addAttribute('catalog_product', 'addons_image', array(
    'group' => 'Images',
    'input' => 'media_image',
    'type' => 'varchar',
    'label' => 'Addons image',
    'frontend' => 'catalog/product_attribute_frontend_image'
));

$installer->endSetup();