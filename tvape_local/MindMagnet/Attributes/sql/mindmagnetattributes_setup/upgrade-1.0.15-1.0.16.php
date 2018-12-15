<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'technical_manufacturer', array(
    'group' => 'Technical Specs',
    'input' => 'text',
    'type' => 'varchar',
    'label' => 'Manufacturer',
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
    'is_configurable' => 1,
    'global' => 0,
    'sort_order' => '0',
));


$installer->endSetup();