<?php
$installer = $this;

/** @var Mage_Eav_Model_Entity_Setup $setup */
$installer->startSetup();

$installer->addAttribute('catalog_product', 'overall_score', array(
    'group' => 'Performance Specs',
    'input' => 'text',
    'type' => 'decimal',
    'label' => 'Overall Score',
    'backend' => '',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable' => 1,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();
