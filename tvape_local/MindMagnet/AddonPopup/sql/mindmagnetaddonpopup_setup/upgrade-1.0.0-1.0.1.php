<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'addon_popup_main_title', array(
    'group' => 'AddOn Popup',
    'input' => 'text',
    'type' => 'varchar',
    'label' => 'Main Title',
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
    'global' => 0,
    'sort_order' => '30',
));


$installer->endSetup();